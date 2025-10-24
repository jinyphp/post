# 관리자: 계시판 포스트 관리 시스템

게시판별 게시글 관리를 위한 Single Action Controller(SAC) 기반 관리 시스템입니다. 생성된 게시판 코드에 따른 개별 테이블(`site_board_{code}`)의 게시글을 전문적으로 관리합니다.

## 시스템 개요

본 모듈은 다중 게시판 시스템에서 각 게시판별로 독립적인 게시글 관리 기능을 제공합니다. 계층형 게시글 구조(부모-자식 관계)를 지원하며, 답글 및 댓글 형태의 중첩 구조를 구현할 수 있습니다.

### 핵심 특징

- **Single Action Controller 패턴**: 각 기능별로 독립된 컨트롤러 클래스 운영
- **동적 테이블 관리**: 게시판 코드별 전용 테이블(`site_board_{code}`) 자동 연결
- **계층형 구조 지원**: 부모-자식 관계를 통한 답글/댓글 시스템
- **실시간 통계 업데이트**: 게시글 수, 조회수, 최근 게시일 자동 계산
- **권한 기반 접근 제어**: 관리자 전용 인터페이스

## 컨트롤러 구조

### AdminBoardPostBase.php
**기본 클래스 | 공통 기능 제공**

모든 BoardPost 컨트롤러의 부모 클래스로서 공통 기능을 제공합니다.

**주요 기능:**
- `getBoardInfo($code)`: 게시판 기본 정보 조회
- `updateBoardStats($code)`: 게시판 통계 정보 실시간 업데이트
- `validateBoard($code)`: 게시판 및 테이블 존재 여부 검증

**통계 업데이트 항목:**
- 게시물 총 개수 (`post`)
- 전체 누적 조회수 (`total_views`)
- 최근 게시글 작성일 (`last_post_at`)

### AdminBoardPostIndex.php
**게시글 목록 조회 | 계층형 정렬**

특정 게시판의 모든 게시글을 계층 구조로 정렬하여 표시합니다.

**주요 기능:**
- 부모-자식 관계 기반 계층형 정렬
- 페이지네이션 지원 (페이지당 15개)
- 각 게시글의 하위글 개수 계산
- 검색 및 필터링 지원

**정렬 방식:**
1. 부모 게시글(parent_id가 null) 우선
2. 동일 부모 내에서 ID 순서
3. 자식 게시글은 부모 게시글 바로 아래 표시

### AdminBoardPostCreate.php
**새 게시글 작성 폼**

새로운 게시글 작성을 위한 폼을 제공합니다.

**제공 기능:**
- 게시글 작성 폼 표시
- 게시판 정보 전달
- 권한 검증

### AdminBoardPostCreateChild.php
**하위글(답글) 작성 폼**

기존 게시글에 대한 답글이나 하위글을 작성하는 폼을 제공합니다.

**특별 기능:**
- 부모 게시글 정보 조회 및 검증
- 부모 글 제목을 폼에 표시
- parent_id 자동 설정
- 계층 구조 유지

### AdminBoardPostStore.php
**게시글 저장 처리**

새로 작성된 게시글을 데이터베이스에 저장합니다.

**처리 과정:**
1. 입력 데이터 검증
2. 게시판 테이블에 데이터 삽입
3. 게시판 통계 업데이트
4. 성공 메시지와 함께 목록으로 리다이렉트

### AdminBoardPostEdit.php
**게시글 수정 폼**

기존 게시글의 내용을 수정하기 위한 폼을 제공합니다.

**제공 정보:**
- 기존 게시글 데이터 로드
- 수정 가능한 모든 필드 표시
- 게시판 및 게시글 정보 검증

### AdminBoardPostUpdate.php
**게시글 업데이트 처리**

수정된 게시글 내용을 데이터베이스에 반영합니다.

**처리 과정:**
1. 수정 권한 검증
2. 입력 데이터 유효성 검사
3. 게시글 데이터 업데이트
4. 게시판 통계 재계산
5. 수정 완료 후 상세보기 또는 목록으로 이동

### AdminBoardPostDelete.php
**게시글 삭제 처리**

게시글을 완전히 삭제합니다.

**삭제 프로세스:**
1. 삭제 권한 확인
2. 하위글 존재 여부 검사
3. 하위글이 있는 경우 연쇄 삭제 또는 오류 처리
4. 게시글 삭제 실행
5. 게시판 통계 업데이트

## 데이터베이스 구조

### 게시판 테이블 (`site_board`)
게시판 기본 정보를 저장하는 마스터 테이블입니다.

**주요 필드:**
- `code`: 게시판 고유 코드
- `title`: 게시판 제목
- `post`: 게시물 총 개수
- `total_views`: 전체 조회수
- `last_post_at`: 최근 게시일

### 게시글 테이블 (`site_board_{code}`)
각 게시판별로 동적 생성되는 게시글 저장 테이블입니다.

**핵심 필드:**
- `id`: 게시글 고유 ID
- `parent_id`: 부모 게시글 ID (답글용)
- `title`: 게시글 제목
- `content`: 게시글 내용
- `click`: 조회수
- `name`: 작성자명
- `email`: 작성자 이메일

## 라우트 구조

```php
// 게시글 관리 라우트 그룹
Route::prefix('posts')->name('posts.')->group(function () {
    Route::get('/{code}', AdminBoardPostIndex::class)->name('index');
    Route::get('/{code}/create', AdminBoardPostCreate::class)->name('create');
    Route::post('/{code}', AdminBoardPostStore::class)->name('store');
    Route::get('/{code}/{id}/edit', AdminBoardPostEdit::class)->name('edit');
    Route::put('/{code}/{id}', AdminBoardPostUpdate::class)->name('update');
    Route::delete('/{code}/{id}', AdminBoardPostDelete::class)->name('destroy');
    Route::get('/{code}/{id}/child/create', AdminBoardPostCreateChild::class)->name('child.create');
});
```

## 사용 방법

### 1. 게시글 목록 조회
`/admin/cms/board/posts/{게시판코드}` 경로로 접근하여 해당 게시판의 모든 게시글을 확인할 수 있습니다.

### 2. 새 게시글 작성
목록 페이지에서 "새 글 작성" 버튼을 클릭하거나 직접 `/admin/cms/board/posts/{게시판코드}/create` 경로로 접근합니다.

### 3. 답글 작성
기존 게시글에서 "답글" 버튼을 클릭하면 해당 게시글의 하위글을 작성할 수 있습니다.

### 4. 게시글 수정/삭제
각 게시글의 액션 버튼을 통해 수정 또는 삭제할 수 있습니다.

## 보안 및 권한

- **관리자 전용**: 모든 기능은 `admin` 미들웨어로 보호됩니다
- **게시판 검증**: 존재하지 않는 게시판 접근 시 자동 차단
- **테이블 검증**: 게시판 테이블 부재 시 오류 처리
- **입력 검증**: 모든 사용자 입력에 대한 유효성 검사

## 확장성

Single Action Controller 패턴을 사용함으로써 다음과 같은 확장이 용이합니다:

- 새로운 기능 추가 시 독립적인 컨트롤러 생성
- 기존 기능 수정 시 해당 컨트롤러만 수정
- 테스트 및 유지보수의 용이성
- 코드 재사용성 향상

이 시스템을 통해 효율적이고 확장 가능한 다중 게시판 관리가 가능합니다.