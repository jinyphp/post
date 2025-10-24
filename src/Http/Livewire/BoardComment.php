<?php

namespace Jiny\Post\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jiny\Post\Http\Controllers\Site\BoardPost\BoardPermissions;

/**
 * Board Comment Livewire Component
 *
 * 게시글 댓글 관리를 위한 Livewire 컴포넌트
 */
class BoardComment extends Component
{
    use BoardPermissions;

    public $code;           // 게시판 코드
    public $postId;         // 게시글 ID
    public $content = '';   // 댓글 내용
    public $comments = [];  // 댓글 목록
    public $board;          // 게시판 정보
    public $canComment = false; // 댓글 작성 권한
    public $replyingTo = null; // 답글 대상 댓글 ID
    public $replyContent = ''; // 답글 내용
    public $editingComment = null; // 수정 중인 댓글 ID
    public $editContent = ''; // 수정할 댓글 내용

    protected $rules = [
        'content' => 'required|string|max:1000',
        'replyContent' => 'required|string|max:1000',
        'editContent' => 'required|string|max:1000',
    ];

    protected $messages = [
        'content.required' => '댓글 내용을 입력해주세요.',
        'content.max' => '댓글은 1000자 이하로 입력해주세요.',
        'replyContent.required' => '답글 내용을 입력해주세요.',
        'replyContent.max' => '답글은 1000자 이하로 입력해주세요.',
        'editContent.required' => '수정할 댓글 내용을 입력해주세요.',
        'editContent.max' => '댓글은 1000자 이하로 입력해주세요.',
    ];

    public function mount($code, $postId)
    {
        $this->code = $code;
        $this->postId = $postId;

        // 게시판 정보 조회
        $this->board = $this->getBoardInfo($code);

        if (!$this->board) {
            return;
        }

        // 댓글 권한 확인
        $this->canComment = $this->hasPermission($this->board, 'comment');

        // 댓글 목록 로드
        $this->loadComments();
    }

    public function render()
    {
        return view('jiny-post::livewire.board-comment');
    }

    /**
     * 댓글 목록 로드
     */
    public function loadComments()
    {
        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";
        $likesTable = $table . "_comment_likes";

        if (!Schema::hasTable($commentTable)) {
            $this->comments = [];
            return;
        }

        $comments = DB::table($commentTable)
            ->where('post_id', $this->postId)
            ->where(function($query) {
                $query->where('is_hidden', false)
                      ->orWhereNull('is_hidden');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // 댓글을 계층 구조로 정렬 (부모 댓글 -> 답글 순서)
        $parentComments = $comments->whereNull('parent_id');
        $replies = $comments->whereNotNull('parent_id')->groupBy('parent_id');

        $sortedComments = collect();
        foreach ($parentComments as $parent) {
            $sortedComments->push($parent);
            if (isset($replies[$parent->id])) {
                foreach ($replies[$parent->id] as $reply) {
                    $reply->is_reply = true;
                    $sortedComments->push($reply);
                }
            }
        }

        $comments = $sortedComments;

        // 각 댓글에 좋아요 정보 추가
        $user = $this->getCurrentUser();
        $userId = Auth::id();
        $userIdentifier = $userId ?: request()->ip();

        foreach ($comments as $comment) {
            // 좋아요 개수 조회
            if (Schema::hasTable($likesTable)) {
                $comment->likes_count = DB::table($likesTable)
                    ->where('comment_id', $comment->id)
                    ->count();

                // 현재 사용자가 좋아요를 눌렀는지 확인
                $comment->user_liked = DB::table($likesTable)
                    ->where('comment_id', $comment->id)
                    ->where(function($query) use ($userId, $userIdentifier) {
                        if ($userId) {
                            $query->where('user_id', $userId);
                        } else {
                            $query->where('user_identifier', $userIdentifier);
                        }
                    })
                    ->exists();
            } else {
                $comment->likes_count = 0;
                $comment->user_liked = false;
            }
        }

        $this->comments = $comments->toArray();
    }

    /**
     * 댓글 저장
     */
    public function store()
    {
        \Log::info('BoardComment store method called', ['content' => $this->content, 'canComment' => $this->canComment]);

        // 권한 확인
        if (!$this->canComment) {
            if (!Auth::check()) {
                $this->dispatch('show-message', [
                    'type' => 'error',
                    'message' => '로그인이 필요합니다.'
                ]);
                return;
            }

            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글 작성 권한이 없습니다.'
            ]);
            return;
        }

        // 입력 검증
        $this->validate([
            'content' => 'required|string|max:1000'
        ], [
            'content.required' => '댓글 내용을 입력해주세요.',
            'content.max' => '댓글은 1000자 이하로 입력해주세요.',
        ]);

        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";

        // 댓글 테이블이 없으면 생성
        if (!Schema::hasTable($commentTable)) {
            $this->createCommentTable($commentTable);
        } else {
            // 기존 테이블에 parent_id 컬럼이 없으면 추가
            if (!Schema::hasColumn($commentTable, 'parent_id')) {
                Schema::table($commentTable, function ($table) {
                    $table->unsignedBigInteger('parent_id')->nullable()->after('uuid');
                    $table->index('parent_id');
                });
            }

            // 기존 테이블에 is_hidden 컬럼이 없으면 추가
            if (!Schema::hasColumn($commentTable, 'is_hidden')) {
                Schema::table($commentTable, function ($table) {
                    $table->boolean('is_hidden')->default(false)->after('parent_id');
                    $table->string('hidden_reason')->nullable()->after('is_hidden');
                    $table->timestamp('hidden_at')->nullable()->after('hidden_reason');
                    $table->index('is_hidden');
                });
            }
        }

        // 사용자 정보 가져오기 (JWT 또는 세션)
        $user = $this->getCurrentUser();

        $data = [
            'post_id' => $this->postId,
            'content' => $this->content,
            'name' => $user ? $user->name : '익명',
            'email' => $user ? $user->email : '',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // user_id 컬럼이 있는 경우에만 추가
        if (Schema::hasColumn($commentTable, 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        // UUID 컬럼이 있는 경우 UUID 생성
        if (Schema::hasColumn($commentTable, 'uuid')) {
            $data['uuid'] = (string) Str::uuid();
        }

        DB::table($commentTable)->insert($data);

        // 입력 필드 초기화
        $this->content = '';

        // 댓글 목록 다시 로드
        $this->loadComments();

        // 성공 메시지 표시
        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => '댓글이 등록되었습니다.'
        ]);
    }

    /**
     * 댓글 삭제
     */
    public function deleteComment($commentId)
    {
        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";

        if (!Schema::hasTable($commentTable)) {
            return;
        }

        $comment = DB::table($commentTable)->find($commentId);

        if (!$comment) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글을 찾을 수 없습니다.'
            ]);
            return;
        }

        // 삭제 권한 확인 (작성자 또는 관리자)
        $user = $this->getCurrentUser();
        $canDelete = false;

        if (Auth::check()) {
            // 작성자 본인이거나
            if ($comment->user_id && $comment->user_id == Auth::id()) {
                $canDelete = true;
            }
            // 관리자이거나
            elseif ($this->hasPermission($this->board, 'delete')) {
                $canDelete = true;
            }
        }

        if (!$canDelete) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글을 삭제할 권한이 없습니다.'
            ]);
            return;
        }

        DB::table($commentTable)->where('id', $commentId)->delete();

        // 댓글 목록 다시 로드
        $this->loadComments();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => '댓글이 삭제되었습니다.'
        ]);
    }

    /**
     * 현재 사용자 정보 가져오기
     */
    private function getCurrentUser()
    {
        if (Auth::check()) {
            return Auth::user();
        }

        // JWT 토큰 확인 (필요한 경우)
        // JWT 로직은 기존 BoardPermissions 트레이트의 setupAuth 메소드 참조

        return null;
    }

    /**
     * 댓글 테이블 생성
     */
    private function createCommentTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('post_id');
            $table->text('content');
            $table->string('name');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('uuid')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // 답글의 부모 댓글 ID
            $table->boolean('is_hidden')->default(false); // 신고 승인 시 숨김 처리용
            $table->string('hidden_reason')->nullable(); // 숨김 사유
            $table->timestamp('hidden_at')->nullable(); // 숨김 처리 시간

            $table->index('post_id');
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('is_hidden');
        });
    }

    /**
     * 댓글 수정 (추후 구현 가능)
     */
    public function editComment($commentId)
    {
        // 수정 기능 구현 시 사용
    }

    /**
     * 댓글 좋아요
     */
    public function likeComment($commentId)
    {
        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";
        $likesTable = $table . "_comment_likes";

        if (!Schema::hasTable($commentTable)) {
            return;
        }

        $comment = DB::table($commentTable)->find($commentId);
        if (!$comment) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글을 찾을 수 없습니다.'
            ]);
            return;
        }

        // 좋아요 테이블이 없으면 생성
        if (!Schema::hasTable($likesTable)) {
            $this->createCommentLikesTable($likesTable);
        }

        $user = $this->getCurrentUser();
        $userId = Auth::id();
        $userIdentifier = $userId ?: request()->ip(); // 비로그인 사용자는 IP로 구분

        // 이미 좋아요를 눌렀는지 확인
        $existingLike = DB::table($likesTable)
            ->where('comment_id', $commentId)
            ->where(function($query) use ($userId, $userIdentifier) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('user_identifier', $userIdentifier);
                }
            })
            ->first();

        if ($existingLike) {
            // 좋아요 취소
            DB::table($likesTable)->where('id', $existingLike->id)->delete();

            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => '좋아요를 취소했습니다.'
            ]);
        } else {
            // 좋아요 추가
            DB::table($likesTable)->insert([
                'comment_id' => $commentId,
                'user_id' => $userId,
                'user_identifier' => $userIdentifier,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => '좋아요를 눌렀습니다.'
            ]);
        }

        // 댓글 목록 다시 로드
        $this->loadComments();
    }

    /**
     * 답글 작성 시작
     */
    public function startReply($commentId)
    {
        $this->replyingTo = $commentId;
        $this->replyContent = '';
    }

    /**
     * 답글 작성 취소
     */
    public function cancelReply()
    {
        $this->replyingTo = null;
        $this->replyContent = '';
    }

    /**
     * 답글 저장
     */
    public function storeReply()
    {
        // 권한 확인
        if (!$this->canComment) {
            if (!Auth::check()) {
                $this->dispatch('show-message', [
                    'type' => 'error',
                    'message' => '로그인이 필요합니다.'
                ]);
                return;
            }

            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '답글 작성 권한이 없습니다.'
            ]);
            return;
        }

        // 입력 검증
        $this->validate(['replyContent' => 'required|string|max:1000'], [
            'replyContent.required' => '답글 내용을 입력해주세요.',
            'replyContent.max' => '답글은 1000자 이하로 입력해주세요.',
        ]);

        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";

        // 댓글 테이블이 없으면 생성
        if (!Schema::hasTable($commentTable)) {
            $this->createCommentTable($commentTable);
        } else {
            // 기존 테이블에 parent_id 컬럼이 없으면 추가
            if (!Schema::hasColumn($commentTable, 'parent_id')) {
                Schema::table($commentTable, function ($table) {
                    $table->unsignedBigInteger('parent_id')->nullable()->after('uuid');
                    $table->index('parent_id');
                });
            }

            // 기존 테이블에 is_hidden 컬럼이 없으면 추가
            if (!Schema::hasColumn($commentTable, 'is_hidden')) {
                Schema::table($commentTable, function ($table) {
                    $table->boolean('is_hidden')->default(false)->after('parent_id');
                    $table->string('hidden_reason')->nullable()->after('is_hidden');
                    $table->timestamp('hidden_at')->nullable()->after('hidden_reason');
                    $table->index('is_hidden');
                });
            }
        }

        // 사용자 정보 가져오기
        $user = $this->getCurrentUser();

        $data = [
            'post_id' => $this->postId,
            'parent_id' => $this->replyingTo,
            'content' => $this->replyContent,
            'name' => $user ? $user->name : '익명',
            'email' => $user ? $user->email : '',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // user_id 컬럼이 있는 경우에만 추가
        if (Schema::hasColumn($commentTable, 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        // UUID 컬럼이 있는 경우 UUID 생성
        if (Schema::hasColumn($commentTable, 'uuid')) {
            $data['uuid'] = (string) Str::uuid();
        }

        DB::table($commentTable)->insert($data);

        // 입력 필드 초기화
        $this->replyContent = '';
        $this->replyingTo = null;

        // 댓글 목록 다시 로드
        $this->loadComments();

        // 성공 메시지 표시
        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => '답글이 등록되었습니다.'
        ]);
    }

    /**
     * 댓글 수정 시작
     */
    public function startEdit($commentId)
    {
        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";

        $comment = DB::table($commentTable)->find($commentId);

        if (!$comment) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글을 찾을 수 없습니다.'
            ]);
            return;
        }

        // 수정 권한 확인 (작성자만 수정 가능)
        $user = $this->getCurrentUser();
        if (!$user || $comment->user_id != $user->id) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글 수정 권한이 없습니다.'
            ]);
            return;
        }

        $this->editingComment = $commentId;
        $this->editContent = $comment->content;
    }

    /**
     * 댓글 수정 취소
     */
    public function cancelEdit()
    {
        $this->editingComment = null;
        $this->editContent = '';
    }

    /**
     * 댓글 수정 저장
     */
    public function updateComment()
    {
        // 입력 검증
        $this->validate([
            'editContent' => 'required|string|max:1000'
        ], [
            'editContent.required' => '수정할 댓글 내용을 입력해주세요.',
            'editContent.max' => '댓글은 1000자 이하로 입력해주세요.',
        ]);

        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";

        $comment = DB::table($commentTable)->find($this->editingComment);

        if (!$comment) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글을 찾을 수 없습니다.'
            ]);
            return;
        }

        // 수정 권한 확인
        $user = $this->getCurrentUser();
        if (!$user || $comment->user_id != $user->id) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글 수정 권한이 없습니다.'
            ]);
            return;
        }

        // 댓글 수정
        DB::table($commentTable)
            ->where('id', $this->editingComment)
            ->update([
                'content' => $this->editContent,
                'updated_at' => now(),
            ]);

        // 수정 상태 초기화
        $this->editingComment = null;
        $this->editContent = '';

        // 댓글 목록 다시 로드
        $this->loadComments();

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => '댓글이 수정되었습니다.'
        ]);
    }

    /**
     * 댓글 신고
     */
    public function reportComment($commentId)
    {
        $table = "site_board_" . $this->code;
        $commentTable = $table . "_comments";
        $reportTable = "site_board_comment_reports"; // 통합 신고 테이블 사용

        // 통합 신고 테이블이 없으면 생성
        if (!Schema::hasTable($reportTable)) {
            $this->createCommentReportTable($reportTable);
        }

        $comment = DB::table($commentTable)->find($commentId);

        if (!$comment) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '댓글을 찾을 수 없습니다.'
            ]);
            return;
        }

        $user = $this->getCurrentUser();
        $userIdentifier = $user ? $user->id : request()->ip();

        // 이미 신고했는지 확인 (게시판 코드와 댓글 ID로 확인)
        $existingReport = DB::table($reportTable)
            ->where('board_code', $this->code)
            ->where('comment_id', $commentId)
            ->where(function($query) use ($user, $userIdentifier) {
                if ($user) {
                    $query->where('reporter_email', $user->email ?? '');
                } else {
                    $query->where('reporter_name', $userIdentifier);
                }
            })
            ->first();

        if ($existingReport) {
            $this->dispatch('show-message', [
                'type' => 'warning',
                'message' => '이미 신고한 댓글입니다.'
            ]);
            return;
        }

        // 신고자 정보 수집
        $reporterName = $user ? $user->name : '익명';
        $reporterEmail = $user ? $user->email : request()->ip();

        // 신고 등록
        try {
            $insertedId = DB::table($reportTable)->insertGetId([
                'board_code' => $this->code,
                'comment_id' => $commentId,
                'reporter_name' => $reporterName,
                'reporter_email' => $reporterEmail,
                'reason' => 'inappropriate',
                'description' => '부적절한 내용으로 신고됨',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('댓글 신고 등록 성공', [
                'report_id' => $insertedId,
                'board_code' => $this->code,
                'comment_id' => $commentId,
                'reporter' => $reporterName
            ]);
        } catch (\Exception $e) {
            \Log::error('댓글 신고 등록 실패', [
                'error' => $e->getMessage(),
                'board_code' => $this->code,
                'comment_id' => $commentId
            ]);

            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => '신고 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
            return;
        }

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => '댓글이 신고되었습니다. 관리자가 검토할 예정입니다.'
        ]);
    }

    /**
     * 댓글 신고 테이블 생성 (통합 테이블)
     */
    private function createCommentReportTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->string('board_code', 100); // 게시판 코드
            $table->unsignedBigInteger('comment_id'); // 댓글 ID
            $table->string('reporter_name', 100); // 신고자명
            $table->string('reporter_email', 255); // 신고자 이메일 (또는 IP)
            $table->enum('reason', ['spam', 'inappropriate', 'abuse', 'copyright', 'other'])->default('inappropriate'); // 신고 사유
            $table->text('description')->nullable(); // 상세 내용
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // 처리 상태
            $table->unsignedBigInteger('reviewed_by')->nullable(); // 검토자 ID
            $table->timestamp('reviewed_at')->nullable(); // 검토 일시

            $table->index(['board_code', 'comment_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * 댓글 좋아요 테이블 생성
     */
    private function createCommentLikesTable($tableName)
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_identifier'); // IP 주소 또는 세션 ID (비로그인 사용자용)

            $table->index('comment_id');
            $table->index(['comment_id', 'user_id']);
            $table->index(['comment_id', 'user_identifier']);
        });
    }
}