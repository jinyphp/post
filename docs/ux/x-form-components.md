# Form 컴포넌트 시리즈

## 개요

HTTP 메소드별로 특화된 폼 컴포넌트들을 제공하여 CRUD 작업을 더 명확하고 일관되게 처리할 수 있습니다. 각 컴포넌트는 해당 HTTP 메소드에 필요한 토큰과 메소드 스푸핑을 자동으로 처리합니다.

## 사용 가능한 컴포넌트

| 컴포넌트 | HTTP 메소드 | 용도 |
|----------|-------------|------|
| `<x-form-post>` | POST | 새 리소스 생성 |
| `<x-form-put>` | PUT | 전체 리소스 업데이트 |
| `<x-form-patch>` | PATCH | 부분 리소스 업데이트 |
| `<x-form-delete>` | DELETE | 리소스 삭제 |

## 공통 속성 (Props)

모든 form 컴포넌트는 다음 속성을 공통으로 지원합니다:

| 속성 | 타입 | 기본값 | 설명 |
|------|------|--------|------|
| `action` | string | `''` | **필수** - 폼 액션 URL |
| `enctype` | string | `null` | 폼 인코딩 타입 (파일 업로드시 `multipart/form-data`) |

## x-form-post

새로운 리소스를 생성할 때 사용합니다.

### 기본 사용법

```blade
<x-form-post action="{{ route('posts.store') }}">
    <input type="text" name="title" placeholder="제목">
    <textarea name="content" placeholder="내용"></textarea>
    <button type="submit">작성</button>
</x-form-post>
```

### 파일 업로드

```blade
<x-form-post action="{{ route('posts.store') }}" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="제목">
    <input type="file" name="image">
    <button type="submit">작성</button>
</x-form-post>
```

### 생성되는 HTML

```html
<form method="POST" action="/posts">
    <input type="hidden" name="_token" value="csrf-token-here">
    <!-- 폼 내용 -->
</form>
```

## x-form-put

전체 리소스를 업데이트할 때 사용합니다. 설정 페이지나 프로필 업데이트에 적합합니다.

### 기본 사용법

```blade
<x-form-put action="{{ route('posts.update', $post) }}">
    <input type="text" name="title" value="{{ $post->title }}">
    <textarea name="content">{{ $post->content }}</textarea>
    <button type="submit">전체 업데이트</button>
</x-form-put>
```

### 설정 업데이트 예시

```blade
<x-form-put action="{{ route('admin.settings.update') }}">
    <x-switch name="enable_comments" :checked="$settings['enable_comments']">
        댓글 기능 활성화
    </x-switch>

    <x-input-number name="max_posts" :value="$settings['max_posts']">
        최대 게시글 수
    </x-input-number>

    <x-btn-save>설정 저장</x-btn-save>
</x-form-put>
```

### 생성되는 HTML

```html
<form method="POST" action="/posts/1">
    <input type="hidden" name="_token" value="csrf-token-here">
    <input type="hidden" name="_method" value="PUT">
    <!-- 폼 내용 -->
</form>
```

## x-form-patch

부분적인 리소스 업데이트에 사용합니다. 단일 필드나 일부 필드만 업데이트할 때 적합합니다.

### 기본 사용법

```blade
<x-form-patch action="{{ route('posts.update', $post) }}">
    <input type="text" name="status" value="published">
    <button type="submit">상태 변경</button>
</x-form-patch>
```

### Ajax와 함께 사용

```blade
<x-form-patch action="{{ route('posts.toggle-featured', $post) }}" id="featured-form">
    <x-switch name="featured" :checked="$post->featured">
        추천 글로 설정
    </x-switch>
</x-form-patch>

<script>
document.getElementById('featured-form').addEventListener('submit', function(e) {
    e.preventDefault();
    // Ajax 처리
});
</script>
```

### 생성되는 HTML

```html
<form method="POST" action="/posts/1">
    <input type="hidden" name="_token" value="csrf-token-here">
    <input type="hidden" name="_method" value="PATCH">
    <!-- 폼 내용 -->
</form>
```

## x-form-delete

리소스 삭제에 사용합니다. 보통 확인 대화상자와 함께 사용됩니다.

### 기본 사용법

```blade
<x-form-delete action="{{ route('posts.destroy', $post) }}">
    <button type="submit"
            class="btn btn-danger"
            onclick="return confirm('정말 삭제하시겠습니까?')">
        삭제
    </button>
</x-form-delete>
```

### 인라인 삭제 버튼

```blade
<td>
    <x-form-delete action="{{ route('posts.destroy', $post) }}" class="d-inline">
        <button type="submit"
                class="btn btn-sm btn-outline-danger"
                onclick="return confirm('삭제하시겠습니까?')">
            <i class="bi bi-trash"></i>
        </button>
    </x-form-delete>
</td>
```

### 생성되는 HTML

```html
<form method="POST" action="/posts/1">
    <input type="hidden" name="_token" value="csrf-token-here">
    <input type="hidden" name="_method" value="DELETE">
    <!-- 폼 내용 -->
</form>
```

## 실제 사용 예시

### 1. 블로그 설정 페이지

```blade
<x-content>
    <x-content-main>
        <x-form-put action="{{ route('admin.blog.config.update') }}">
            <!-- 설정 카드들 -->
            <div class="card">
                <div class="card-body">
                    <x-switch name="enable_comments" :checked="$config['enable_comments']">
                        댓글 기능 활성화
                    </x-switch>

                    <x-input-number name="max_posts" :value="$config['max_posts']">
                        최대 게시글 수
                    </x-input-number>
                </div>
            </div>

            <x-btn-save>설정 저장</x-btn-save>
        </x-form-put>
    </x-content-main>

    <x-content-side>
        <x-help title="설정 도움말">
            설정 변경 시 주의사항들...
        </x-help>
    </x-content-side>
</x-content>
```

### 2. 게시글 관리 테이블

```blade
<table class="table">
    <thead>
        <tr>
            <th>제목</th>
            <th>상태</th>
            <th>작업</th>
        </tr>
    </thead>
    <tbody>
        @foreach($posts as $post)
        <tr>
            <td>{{ $post->title }}</td>
            <td>
                <x-form-patch action="{{ route('posts.toggle-status', $post) }}">
                    <x-switch name="published" :checked="$post->published">
                        발행됨
                    </x-switch>
                </x-form-patch>
            </td>
            <td>
                <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-primary">수정</a>
                <x-form-delete action="{{ route('posts.destroy', $post) }}" class="d-inline">
                    <button type="submit"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('삭제하시겠습니까?')">
                        삭제
                    </button>
                </x-form-delete>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### 3. 멀티 액션 폼

```blade
<!-- 대량 삭제 -->
<x-form-delete action="{{ route('posts.bulk-destroy') }}">
    @foreach($selectedPosts as $post)
        <input type="hidden" name="ids[]" value="{{ $post->id }}">
    @endforeach
    <button type="submit" class="btn btn-danger">선택된 항목 삭제</button>
</x-form-delete>

<!-- 대량 상태 변경 -->
<x-form-patch action="{{ route('posts.bulk-update') }}">
    @foreach($selectedPosts as $post)
        <input type="hidden" name="ids[]" value="{{ $post->id }}">
    @endforeach
    <select name="status">
        <option value="published">발행</option>
        <option value="draft">초안</option>
    </select>
    <button type="submit" class="btn btn-primary">상태 변경</button>
</x-form-patch>
```

## 보안 고려사항

1. **CSRF 보호**: 모든 컴포넌트에서 자동으로 CSRF 토큰이 포함됩니다.
2. **메소드 스푸핑**: PUT, PATCH, DELETE는 자동으로 메소드 스푸핑이 적용됩니다.
3. **권한 검증**: 라우트에서 적절한 권한 검증을 수행해야 합니다.

```php
// routes/web.php
Route::put('/admin/config', [ConfigController::class, 'update'])
    ->middleware(['admin'])  // 관리자 권한 필요
    ->name('admin.config.update');
```

## 모범 사례

### 1. 의미에 맞는 HTTP 메소드 선택

```blade
<!-- ✅ 좋은 예: 설정 전체 업데이트 -->
<x-form-put action="{{ route('settings.update') }}">
    <!-- 모든 설정 필드들 -->
</x-form-put>

<!-- ✅ 좋은 예: 단일 상태 토글 -->
<x-form-patch action="{{ route('posts.toggle-featured', $post) }}">
    <input type="hidden" name="featured" value="{{ !$post->featured }}">
</x-form-patch>

<!-- ❌ 나쁜 예: 단일 필드 변경에 PUT 사용 -->
<x-form-put action="{{ route('posts.update', $post) }}">
    <input type="hidden" name="featured" value="1">
</x-form-put>
```

### 2. 적절한 확인 메시지

```blade
<!-- ✅ 좋은 예: 삭제 시 확인 -->
<x-form-delete action="{{ route('posts.destroy', $post) }}">
    <button type="submit"
            onclick="return confirm('이 게시글을 삭제하시겠습니까?')">
        삭제
    </button>
</x-form-delete>

<!-- ✅ 좋은 예: 중요한 설정 변경 시 확인 -->
<x-form-put action="{{ route('admin.danger-settings.update') }}">
    <button type="submit"
            onclick="return confirm('이 설정을 변경하면 시스템에 영향을 줄 수 있습니다. 계속하시겠습니까?')">
        위험한 설정 저장
    </button>
</x-form-put>
```

### 3. 파일 업로드 처리

```blade
<!-- ✅ 좋은 예: 파일 업로드 시 enctype 지정 -->
<x-form-post action="{{ route('posts.store') }}" enctype="multipart/form-data">
    <input type="file" name="image" accept="image/*">
    <button type="submit">업로드</button>
</x-form-post>
```

## 주의사항

1. **action 속성 필수**: 모든 form 컴포넌트에서 `action` 속성은 반드시 제공해야 합니다.
2. **라우트 일치**: 사용하는 HTTP 메소드와 라우트 정의가 일치해야 합니다.
3. **브라우저 호환성**: PUT, PATCH, DELETE는 메소드 스푸핑을 통해 구현되므로 JavaScript 비활성화 환경에서도 동작합니다.
4. **Ajax 사용 시**: 폼 전송을 Ajax로 처리할 때는 적절한 이벤트 핸들링이 필요합니다.

## 관련 컴포넌트

- [`x-btn-save`](./x-btn-save.md): 저장 버튼 컴포넌트
- [`x-switch`](./x-switch.md): 토글 스위치 컴포넌트
- [`x-input-number`](./x-input-number.md): 숫자 입력 컴포넌트
- [`x-content`](./x-content.md): 레이아웃 컴포넌트