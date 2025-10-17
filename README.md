# Jiny Post Package

Laravel 패키지로 게시판(Board), 블로그(Blog), 포스트(Post), Q&A(QNA) 기능을 제공합니다.

## 설치

```bash
composer require jiny/post
```

## 설정

### 서비스 프로바이더 등록 (Laravel 11+ Auto Discovery 지원)

Laravel 11 이상에서는 자동으로 서비스 프로바이더가 등록됩니다.

### 설정 파일 발행

```bash
php artisan vendor:publish --tag=jiny-post-config
```

### 마이그레이션 실행

```bash
php artisan migrate
```

### 뷰 파일 발행 (선택사항)

```bash
php artisan vendor:publish --tag=jiny-post-views
```

## 기능

### 게시판 (Board)
- 다중 게시판 지원
- 계층형 댓글 시스템
- 파일 첨부 기능
- 좋아요/평점 시스템
- 게시글 승인 시스템

### 블로그 (Blog)
- SEO 최적화
- 태그 및 카테고리 지원
- 댓글 시스템
- 소셜 미디어 연동

### 포스트 (Post)
- 일반적인 포스트 기능
- 투표 시스템
- 승인 워크플로우

### Q&A (QNA)
- 질문과 답변 시스템
- 베스트 답변 선택
- 자동 마감 기능
- 투표 시스템

## 설정 옵션

`config/post.php` 파일에서 다양한 옵션을 설정할 수 있습니다:

```php
'board' => [
    'enable' => true,
    'pagination' => 15,
    'allow_anonymous' => true,
    'enable_comments' => true,
    'enable_ratings' => true,
],
```

## 라우트

### 관리자 라우트
- `/admin/cms/board` - 게시판 관리
- `/admin/cms/board/list` - 게시판 목록
- `/admin/cms/board/posts/{code}` - 게시글 관리

### 사용자 라우트
- `/board` - 게시판 목록
- `/blog` - 블로그
- `/post` - 포스트
- `/qna` - Q&A

## 뷰 커스터마이징

뷰 파일을 커스터마이징하려면:

```bash
php artisan vendor:publish --tag=jiny-post-views
```

발행된 뷰 파일은 `resources/views/vendor/jiny-post/` 디렉토리에 위치합니다.

## 권한

관리자 기능은 `admin` 미들웨어를 사용합니다. `jiny/admin` 패키지가 필요합니다.

## 라이선스

MIT License

## 지원

문의사항이나 버그 리포트는 GitHub Issues를 이용해주세요.