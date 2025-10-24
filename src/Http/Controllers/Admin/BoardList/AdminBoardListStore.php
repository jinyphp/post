<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 게시판 데이터 저장 (권한 관리 및 테이블 생성 통합)
 */
class AdminBoardListStore extends AdminBoardListBase
{
    public function __invoke(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        // 체크박스 처리
        $data['enable'] = $request->has('enable') ? 1 : 0;

        // 권한 설정 처리
        $data['write_permission'] = $request->get('write_permission', 'guest_allowed');
        $data['read_permission'] = $request->get('read_permission', 'guest_allowed');
        $data['comment_permission'] = $request->get('comment_permission', 'guest_allowed');

        // 코드가 제공되지 않았다면 타이틀명 hash코드를 기반으로 신규 테이블을 생성합니다.
        if (isset($data['title']) && $data['title']) {
            if (empty($data['code'])) {
                $code = md5($data['title'] . date("Y-m-d_H:i:s"));
                $code = substr($code, 0, 7);
                $data['code'] = $code;
            } else {
                $code = $data['code'];

                // 중복 코드 검사
                $existingBoard = DB::table($this->table)->where('code', $code)->first();
                if ($existingBoard) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['code' => '이미 존재하는 게시판 코드입니다.']);
                }
            }

            // slug가 비어있으면 code 값을 사용
            if (empty($data['slug'])) {
                $data['slug'] = $code;
            }

            // 권한 설정을 테이블 생성에 전달
            $permissions = [
                'write_permission' => $data['write_permission'],
                'read_permission' => $data['read_permission'],
                'comment_permission' => $data['comment_permission'],
            ];

            // 게시판 테이블과 코멘트 테이블, 평가 테이블을 생성합니다.
            // (MigrateRatingTableController 기능 통합)
            $this->createBoardTables($code, $permissions);
        }

        // 타임스탬프 추가
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // 게시판 정보 저장
        $boardId = DB::table($this->table)->insertGetId($data);

        // 게시판 통계 초기화
        $this->updateBoardStats($boardId);

        return redirect()->route('admin.cms.board.list.index')
            ->with('success', '게시판이 생성되었습니다. 권한 설정: ' . $this->permissionOptions[$data['write_permission']]);
    }
}