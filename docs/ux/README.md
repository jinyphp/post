# JinyPost UX 컴포넌트 가이드

## 개요

JinyPost 패키지는 관리자 인터페이스의 일관성과 개발 효율성을 위해 재사용 가능한 Blade 컴포넌트를 제공합니다. 이 문서는 Claude나 다른 AI 도구가 Blade 화면을 구현할 때 참고할 수 있도록 작성되었습니다.

## 📦 사용 가능한 컴포넌트

### 1. [x-switch](./x-switch.md)
토글 스위치 컴포넌트로 boolean 설정값을 입력받을 때 사용합니다.

```blade
<x-switch name="enable_feature" :checked="true">
    <strong>기능 활성화</strong>
    <div class="text-muted small">이 기능을 활성화합니다.</div>
</x-switch>
```

### 2. [x-input-number](./x-input-number.md)
숫자 입력을 위한 컴포넌트로 라벨, 입력 필드, 설명을 통합합니다.

```blade
<x-input-number name="max_items" :value="10" :min="1" :max="100">
    <strong>최대 아이템 수</strong>
    <div class="text-muted small">설정할 수 있는 최대 아이템 개수입니다.</div>
</x-input-number>
```

### 3. [x-btn-save](./x-btn-save.md)
폼 저장을 위한 일관된 버튼 컴포넌트입니다.

```blade
<x-btn-save>설정 저장</x-btn-save>
```

## 🎯 컴포넌트 선택 가이드

### 언제 x-switch를 사용하나요?

✅ **사용해야 할 때:**
- Boolean 값 (true/false, enabled/disabled) 설정
- 기능 활성화/비활성화 토글
- 정책 또는 권한 설정
- 사용자 환경설정

❌ **사용하지 말아야 할 때:**
- 다중 선택 옵션 (라디오 버튼 또는 셀렉트 박스 사용)
- 숫자나 텍스트 입력 (x-input-number 또는 기본 input 사용)
- 파일 업로드

### 언제 x-input-number를 사용하나요?

✅ **사용해야 할 때:**
- 숫자 값 입력 (개수, 크기, 길이 등)
- 범위가 정해진 설정값
- 최소/최대 제한이 있는 입력
- 단위가 있는 숫자 (MB, 초, 개 등)

❌ **사용하지 말아야 할 때:**
- Boolean 값 (x-switch 사용)
- 텍스트 입력 (기본 input 사용)
- 날짜/시간 입력 (date/time input 사용)
- 매우 큰 범위의 숫자 (검색, ID 등)

### 언제 x-btn-save를 사용하나요?

✅ **사용해야 할 때:**
- 폼 데이터 저장
- 설정 저장
- 데이터 업데이트
- 정보 제출

❌ **사용하지 말아야 할 때:**
- 페이지 이동 (링크 사용)
- 데이터 삭제 (다른 스타일의 버튼 사용)
- 모달 열기/닫기

## 🏗️ 컴포넌트 구조

### 컴포넌트 파일 위치

```
vendor/jiny/post/resources/views/components/
├── switch.blade.php
├── input-number.blade.php
└── btn-save.blade.php
```

### 등록 위치

컴포넌트는 `JinyPostServiceProvider`에서 등록됩니다:

```php
// vendor/jiny/post/src/Providers/JinyPostServiceProvider.php
protected function loadBladeComponents(): void
{
    Blade::component('jiny-post::components.switch', 'switch');
    Blade::component('jiny-post::components.input-number', 'input-number');
    Blade::component('jiny-post::components.btn-save', 'btn-save');
}
```

## 🎨 디자인 시스템

### 색상 체계

- **Primary**: Bootstrap의 primary 색상 (파란색)
- **텍스트**: 기본 다크 색상, 설명은 muted 색상
- **배경**: 주로 흰색, 카드는 연한 회색 테두리

### 간격 체계

- **컴포넌트 간격**: `mb-3` (하단 마진)
- **버튼 영역**: `mb-4` (하단 마진)
- **아이콘 간격**: `me-1` (우측 마진)

### 폰트 체계

- **라벨**: `<strong>` 태그로 굵게
- **설명**: `text-muted small` 클래스

## 🔧 실제 구현 예시

### 설정 페이지 전체 구조

```blade
@extends($layout ?? 'jiny-site::layouts.admin.sidebar')

@section('title', '설정 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤딩 -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <h3><i class="bi bi-gear me-2 text-primary"></i>설정 관리</h3>
            <p class="text-muted mb-0">시스템 설정을 관리합니다.</p>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.config.save') }}">
                @csrf

                <!-- 정책 설정 -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-check me-2 text-primary"></i>
                            정책 설정
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <x-switch name="admin_write" :checked="$config['policies']['admin_write']['enabled']">
                                    <strong>관리자 작성 허용</strong>
                                    <div class="text-muted small">시스템 관리자가 글을 작성할 수 있습니다.</div>
                                </x-switch>

                                <x-switch name="user_write" :checked="$config['policies']['user_write']['enabled']">
                                    <strong>일반 사용자 작성 허용</strong>
                                    <div class="text-muted small">로그인한 일반 사용자가 글을 작성할 수 있습니다.</div>
                                </x-switch>
                            </div>

                            <div class="col-md-6">
                                <x-switch name="user_approval" :checked="$config['policies']['user_approval']['enabled']">
                                    <strong>사용자 글 승인 필요</strong>
                                    <div class="text-muted small">일반 사용자의 글은 관리자 승인 후 발행됩니다.</div>
                                </x-switch>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 기본 설정 -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2 text-primary"></i>
                            기본 설정
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <x-input-number
                                    name="max_images"
                                    :value="$config['settings']['max_images']"
                                    :min="1"
                                    :max="50"
                                >
                                    <strong>최대 이미지 수</strong>
                                    <div class="text-muted small">글당 업로드할 수 있는 최대 이미지 수입니다.</div>
                                </x-input-number>
                            </div>

                            <div class="col-md-6">
                                <x-switch name="enable_comments" :checked="$config['settings']['enable_comments']">
                                    <strong>댓글 기능 활성화</strong>
                                    <div class="text-muted small">글에 댓글 기능을 활성화합니다.</div>
                                </x-switch>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <x-btn-save>설정 저장</x-btn-save>
            </form>
        </div>

        <!-- 사이드바 정보 -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2 text-info"></i>
                        설정 정보
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">마지막 업데이트</small>
                        <div class="fw-medium">{{ $config['updated_at'] ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## 📝 코딩 규칙

### 1. 컴포넌트 사용 원칙

- **일관성**: 같은 기능에는 같은 컴포넌트 사용
- **단순성**: 복잡한 로직은 컴포넌트 내부가 아닌 컨트롤러에서 처리
- **재사용성**: 프로젝트 전반에서 재사용 가능하도록 설계

### 2. 네이밍 규칙

- **컴포넌트**: kebab-case 사용 (`x-switch`, `x-btn-save`)
- **속성**: camelCase 사용 (`name`, `checked`, `hiddenValue`)
- **슬롯**: 시맨틱한 HTML 구조 유지

### 3. 접근성 고려사항

- 라벨과 input 연결
- 키보드 네비게이션 지원
- 스크린 리더 호환성

## 🚀 개발 모범 사례

### 1. 폼 구조

```blade
<form method="POST" action="{{ route('admin.save') }}">
    @csrf

    <!-- 설정 그룹 -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>설정 그룹명</h5>
        </div>
        <div class="card-body">
            <!-- 컴포넌트들 -->
        </div>
    </div>

    <!-- 저장 버튼 -->
    <x-btn-save>저장</x-btn-save>
</form>
```

### 2. 에러 처리

```blade
@if($errors->any())
    <div class="alert alert-danger">
        <!-- 에러 메시지 -->
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
```

### 3. 레이아웃 구조

```blade
<div class="row">
    <div class="col-lg-8">
        <!-- 메인 폼 -->
    </div>
    <div class="col-lg-4">
        <!-- 사이드바 정보 -->
    </div>
</div>
```

## 🔍 참고 자료

- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- [Laravel Blade Components](https://laravel.com/docs/blade#components)
- [Bootstrap Icons](https://icons.getbootstrap.com/)

---

이 문서는 JinyPost 패키지의 UX 컴포넌트를 효율적으로 활용하기 위한 가이드입니다. 새로운 컴포넌트가 추가되거나 기존 컴포넌트가 업데이트될 때마다 이 문서도 함께 업데이트됩니다.