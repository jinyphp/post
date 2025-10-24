<?php
namespace Jiny\Post\Http\Controllers\Admin\BoardList;

use Illuminate\Http\Request;

/**
 * 기존 게시판에 평가 테이블 추가 마이그레이션
 * (MigrateRatingTableController 기능 통합)
 */
class AdminBoardListMigrateRating extends AdminBoardListBase
{
    public function __invoke(Request $request)
    {
        try {
            // 베이스 클래스의 마이그레이션 기능 사용
            $result = $this->migrateRatingTables();

            $message = [];
            if ($result['created_count'] > 0) {
                $message[] = $result['created_count'] . "개 평가 테이블이 생성되었습니다: " . implode(', ', $result['created']);
            }
            if ($result['skipped_count'] > 0) {
                $message[] = $result['skipped_count'] . "개 테이블은 이미 존재합니다: " . implode(', ', $result['skipped']);
            }

            if (empty($message)) {
                $message[] = "처리할 게시판이 없습니다.";
            }

            return response()->json([
                'success' => true,
                'message' => implode("\n", $message),
                'created' => $result['created_count'],
                'skipped' => $result['skipped_count'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Rating table migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => '평가 테이블 생성 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }
}