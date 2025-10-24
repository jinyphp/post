<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 게시판 데이터 수정 (권한 관리 포함)
 */
class AdminBoardListUpdate extends AdminBoardListBase
{
    public function __invoke(Request $request, $id)
    {
        $data = $request->except(['_token', '_method']);

        // 체크박스 처리
        $data['enable'] = $request->has('enable') ? 1 : 0;

        // 권한 설정 처리
        $data['write_permission'] = $request->get('write_permission', 'guest_allowed');
        $data['read_permission'] = $request->get('read_permission', 'guest_allowed');
        $data['comment_permission'] = $request->get('comment_permission', 'guest_allowed');

        // 현재 게시판 정보 가져오기
        $currentBoard = DB::table($this->table)->find($id);

        if (!$currentBoard) {
            return redirect()->route('admin.cms.board.list.index')
                ->with('error', '게시판을 찾을 수 없습니다.');
        }

        // slug가 비어있으면 code 값을 사용
        if (empty($data['slug']) && $currentBoard && $currentBoard->code) {
            $data['slug'] = $currentBoard->code;
        }

        // 코드는 변경이 불가능합니다.
        unset($data['code']);

        // 타임스탬프 추가
        $data['updated_at'] = now();

        // 게시판 정보 업데이트
        DB::table($this->table)
            ->where('id', $id)
            ->update($data);

        // 게시판 통계 업데이트
        $this->updateBoardStats($id);

        $permissionMessage = $this->permissionOptions[$data['write_permission']] ?? '';

        return redirect()->route('admin.cms.board.list.index')
            ->with('success', '게시판이 수정되었습니다. 권한 설정: ' . $permissionMessage);
    }
}