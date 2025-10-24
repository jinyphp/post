# 관리자: 다중 계시판 관리 시스템

코드와 slug 기반의 멀티 테이블 게시판 시스템입니다. 각 게시판마다 독립적인 테이블을 생성하고 관리하며, 세밀한 권한 제어를 통해 확장 가능한 게시판 환경을 제공합니다.

## 🚀 주요 특징

- **코드 기반 멀티 테이블**: 게시판별 고유 코드로 독립적인 테이블 관리
- **Slug 지원**: SEO 친화적인 URL 구조
- **세밀한 권한 관리**: 읽기/쓰기/댓글 권한별 독립 제어
- **자동 테이블 생성**: 게시판 생성 시 관련 테이블 자동 생성
- **Single Action Controller**: 기능별 독립된 컨트롤러로 유지보수성 향상
- **통합 평가 시스템**: 좋아요/별점 시스템 내장
- **실시간 통계**: 게시글 수, 조회수, 최근 활동 추적

## 시스템 개요

본 모듈은 복수의 게시판을 통합 관리하며, 각 게시판마다 고유한 권한 체계와 독립적인 데이터베이스 구조를 제공합니다. 게시판 생성 시 자동으로 관련 테이블들이 생성되며, 권한 기반 접근 제어가 구현됩니다.

### 핵심 특징

- **단일 작업 컨트롤러(SAC) 패턴**: 각 기능별로 독립된 컨트롤러 클래스 운영
- **동적 테이블 생성**: 게시판별 전용 테이블 자동 생성 (`site_board_{code}`)
- **세밀한 권한 관리**: 읽기/쓰기/댓글 권한별 독립 제어
- **통합 평가 시스템**: 좋아요/별점 시스템 내장
- **실시간 통계**: 게시글 수, 조회수, 최근 활동 추적
- **샤딩 지원**: 대용량 데이터 처리를 위한 분산 구조

## 권한 시스템

### 권한 유형
게시판별로 세 가지 권한을 독립적으로 설정할 수 있습니다:

1. **글쓰기 권한 (write_permission)**
   - `admin_only`: 관리자만 글쓰기 허용
   - `member_only`: 회원만 글쓰기 허용
   - `guest_allowed`: 비회원도 글쓰기 허용

2. **읽기 권한 (read_permission)**
   - `admin_only`: 관리자만 읽기 허용
   - `member_only`: 회원만 읽기 허용
   - `guest_allowed`: 비회원도 읽기 허용

3. **댓글 권한 (comment_permission)**
   - `admin_only`: 관리자만 댓글 허용
   - `member_only`: 회원만 댓글 허용
   - `guest_allowed`: 비회원도 댓글 허용

## 컨트롤러 구조

### AdminBoardListBase.php
**기본 클래스 | 공통 기능 및 스키마 관리**

모든 BoardList 컨트롤러의 부모 클래스로서 핵심 기능을 제공합니다.

**주요 기능:**
- `createBoardTables($code, $permissions)`: 게시판 관련 테이블 일괄 생성
- `schemaCreate($schema, $permissions)`: 게시판 테이블 스키마 생성 (권한 필드 포함)
- `createCommentTable($tableName)`: 댓글 테이블 생성
- `createRatingTable($tableName)`: 평가 테이블 생성 (좋아요/별점)
- `checkBoardPermission($boardCode, $permissionType, $userId, $isAdmin)`: 권한 검증
- `updateBoardStats($boardId)`: 게시판 통계 업데이트

**생성되는 테이블:**
1. **메인 게시판 테이블** (`site_board_{code}`)
   - 게시글 저장 및 권한 필드 포함
   - 계층형 구조 지원 (parent_id, level)
   - 샤딩 지원 (uuid, shard_id)

2. **댓글 테이블** (`site_board_{code}_comments`)
   - 게시글별 댓글 저장
   - 사용자 정보 및 샤딩 지원

3. **평가 테이블** (`site_board_{code}_ratings`)
   - 좋아요/별점 시스템
   - 중복 평가 방지 (유니크 제약)

### AdminBoardListIndex.php
**게시판 목록 조회 | 검색 및 필터링**

모든 게시판을 목록으로 표시하며 다양한 검색 및 필터 기능을 제공합니다.

**주요 기능:**
- 통합 검색 (제목, 코드, 슬러그, 부제목)
- 상태 필터링 (활성/비활성)
- 권한별 필터링
- 실시간 통계 계산 및 업데이트
- 페이지네이션 (페이지당 15개)

**실시간 통계:**
- 게시글 수 (`post_count`)
- 총 조회수 (`total_views`)
- 테이블 존재 여부 검증

### AdminBoardListCreate.php
**게시판 생성 폼**

새로운 게시판 생성을 위한 폼을 제공합니다.

**제공 기능:**
- 게시판 기본 정보 입력 폼
- 권한 설정 인터페이스
- 유효성 검증

### AdminBoardListStore.php
**게시판 생성 처리 | 테이블 자동 생성**

새로운 게시판을 생성하고 관련 테이블들을 자동으로 생성합니다.

**처리 과정:**
1. 입력 데이터 검증 및 처리
2. 게시판 코드 자동 생성 (MD5 해시 기반)
3. 권한 설정 처리
4. 게시판 테이블 및 연관 테이블 생성
5. 게시판 정보 데이터베이스 저장
6. 통계 초기화

**자동 생성 기능:**
- 고유 코드 생성 (7자리 MD5 해시)
- 슬러그 자동 설정
- 권한 기본값 적용

### AdminBoardListEdit.php
**게시판 수정 폼**

기존 게시판의 설정을 수정하기 위한 폼을 제공합니다.

**제공 정보:**
- 기존 게시판 데이터 로드
- 권한 설정 수정 인터페이스
- 유효성 검증

### AdminBoardListUpdate.php
**게시판 업데이트 처리**

수정된 게시판 설정을 데이터베이스에 반영합니다.

**처리 과정:**
1. 수정 권한 검증
2. 입력 데이터 유효성 검사
3. 권한 설정 업데이트
4. 게시판 정보 업데이트
5. 통계 재계산

### AdminBoardListDelete.php
**게시판 삭제 처리 | 관련 테이블 일괄 삭제**

게시판과 모든 관련 데이터를 완전히 삭제합니다.

**삭제 프로세스:**
1. 게시판 존재 여부 확인
2. 연관 테이블 삭제
   - 메인 게시판 테이블 (`site_board_{code}`)
   - 댓글 테이블 (`site_board_{code}_comments`)
   - 평가 테이블 (`site_board_{code}_ratings`)
3. 게시판 정보 삭제

### AdminBoardListPermission.php
**권한 관리 전용 | API 기반**

게시판별 권한 설정 및 통계를 제공하는 API 컨트롤러입니다.

**주요 기능:**
- 특정 게시판 권한 정보 조회 (JSON API)
- 전체 게시판 권한 통계
- 권한별 게시판 수 집계
- 일괄 권한 업데이트 (`bulkUpdate`)

**API 엔드포인트:**
- `GET /permissions/{boardCode}`: 특정 게시판 권한 조회
- `GET /permissions`: 권한 통계 조회
- `POST /permissions/bulk-update`: 권한 일괄 업데이트

### AdminBoardListMigrateRating.php
**평가 테이블 마이그레이션 | 레거시 지원**

기존 게시판에 평가 테이블을 추가하는 마이그레이션 기능을 제공합니다.

**기능:**
- 기존 모든 게시판에 평가 테이블 추가
- 중복 생성 방지
- 생성/건너뛴 테이블 통계 제공
- 에러 핸들링 및 로깅

## 데이터베이스 구조

### 마스터 게시판 테이블 (`site_board`)
게시판 기본 정보와 권한 설정을 저장하는 중앙 테이블입니다.

**핵심 필드:**
- `code`: 게시판 고유 코드 (테이블명 생성용)
- `title`: 게시판 제목
- `write_permission`: 글쓰기 권한 설정
- `read_permission`: 읽기 권한 설정
- `comment_permission`: 댓글 권한 설정
- `post`: 게시글 총 개수
- `total_views`: 전체 조회수
- `last_post_at`: 최근 게시일

### 동적 게시판 테이블 (`site_board_{code}`)
각 게시판별로 생성되는 게시글 저장 테이블입니다.

**주요 구조:**
- **기본 정보**: id, title, content, 작성자 정보
- **권한 필드**: write_permission, read_permission, comment_permission
- **계층 구조**: parent_id, level (답글/댓글 지원)
- **샤딩 지원**: uuid, shard_id
- **통계**: click(조회수), like(좋아요), rank(랭킹)

### 댓글 테이블 (`site_board_{code}_comments`)
게시글별 댓글을 저장하는 테이블입니다.

**주요 필드:**
- `post_id`: 연결된 게시글 ID
- 작성자 정보 및 내용
- 샤딩 지원 구조

### 평가 테이블 (`site_board_{code}_ratings`)
좋아요 및 별점 평가를 저장하는 테이블입니다.

**특징:**
- 평가 유형별 구분 (좋아요/별점)
- 중복 평가 방지 (사용자당/IP당 한 번)
- 샤딩 지원

## 라우트 구조

```php
// 게시판 목록 관리 라우트
Route::prefix('list')->name('list.')->group(function () {
    Route::get('/', AdminBoardListIndex::class)->name('index');
    Route::get('/create', AdminBoardListCreate::class)->name('create');
    Route::post('/', AdminBoardListStore::class)->name('store');
    Route::get('/{id}/edit', AdminBoardListEdit::class)->name('edit');
    Route::put('/{id}', AdminBoardListUpdate::class)->name('update');
    Route::delete('/{id}', AdminBoardListDelete::class)->name('destroy');

    // 권한 관리
    Route::get('/permissions/{boardCode?}', AdminBoardListPermission::class)->name('permissions');
    Route::post('/permissions/bulk-update', [AdminBoardListPermission::class, 'bulkUpdate'])->name('permissions.bulk-update');
});

// 평가 테이블 마이그레이션
Route::post('/migrate-rating-tables', AdminBoardListMigrateRating::class)->name('migrate.rating.tables');
```

## 사용 방법

### 1. 새 게시판 생성
1. `/admin/cms/board/list/create` 경로로 접근
2. 게시판 기본 정보 입력 (제목, 부제목 등)
3. 권한 설정 선택 (읽기/쓰기/댓글 권한)
4. 저장 시 자동으로 관련 테이블 생성

### 2. 게시판 목록 관리
1. `/admin/cms/board/list` 경로에서 전체 게시판 조회
2. 검색 기능으로 특정 게시판 찾기
3. 상태 및 권한별 필터링
4. 실시간 통계 확인 (게시글 수, 조회수)

### 3. 권한 관리
1. 개별 게시판 권한 수정
2. API를 통한 권한 정보 조회
3. 여러 게시판 권한 일괄 변경

### 4. 기존 게시판 마이그레이션
1. 평가 테이블 마이그레이션 실행
2. 레거시 게시판에 새로운 기능 추가

## 보안 및 권한

- **관리자 전용**: 모든 기능은 `admin` 미들웨어로 보호
- **계층적 권한**: 게시판별 세밀한 권한 제어
- **데이터 검증**: 모든 입력에 대한 유효성 검사
- **SQL 인젝션 방지**: Query Builder 사용
- **중복 방지**: 평가 시스템의 중복 제출 차단

## 확장성 및 성능

### 확장성
- **SAC 패턴**: 새로운 기능 추가 시 독립적인 컨트롤러 생성
- **모듈화**: 각 기능별 독립적인 유지보수
- **API 지원**: 권한 관리 API로 외부 시스템 연동 가능

### 성능 최적화
- **샤딩 지원**: 대용량 데이터 분산 처리
- **인덱스 최적화**: 권한 및 계층 구조를 위한 복합 인덱스
- **통계 캐싱**: 실시간 통계 계산 및 DB 저장
- **지연 로딩**: 필요시에만 통계 계산

## 모니터링 및 로깅

- **에러 로깅**: 마이그레이션 및 테이블 생성 오류 추적
- **권한 검증 로그**: 접근 권한 검증 결과 기록
- **성능 모니터링**: 통계 계산 및 업데이트 성능 추적

이 시스템을 통해 확장 가능하고 안전한 다중 게시판 환경을 구축할 수 있으며, 세밀한 권한 제어와 효율적인 데이터 관리가 가능합니다.