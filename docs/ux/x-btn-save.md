# x-btn-save 컴포넌트

## 개요

`<x-btn-save>` 컴포넌트는 폼의 저장 기능을 위한 일관된 버튼 컴포넌트입니다. Bootstrap의 버튼 스타일을 기반으로 하며, 아이콘과 텍스트를 포함한 저장 버튼을 간편하게 생성할 수 있습니다.

## 기본 사용법

```blade
<x-btn-save>설정 저장</x-btn-save>
```

## 속성 (Props)

| 속성 | 타입 | 기본값 | 설명 |
|------|------|--------|------|
| `type` | string | `'submit'` | 버튼의 type 속성 (submit, button, reset) |
| `icon` | string | `'bi-check-circle'` | Bootstrap Icons 클래스명 |
| `size` | string | `''` | 버튼 크기 (btn-sm, btn-lg 등) |
| `disabled` | boolean | `false` | 버튼 비활성화 상태 |

## 슬롯 (Slot)

버튼의 텍스트 내용은 `$slot`을 통해 전달됩니다.

```blade
<x-btn-save>저장하기</x-btn-save>
```

## 사용 예시

### 1. 기본 저장 버튼

```blade
<x-btn-save>설정 저장</x-btn-save>
```

### 2. 커스텀 아이콘

```blade
<x-btn-save icon="bi-cloud-upload">클라우드에 저장</x-btn-save>
```

### 3. 아이콘 없는 버튼

```blade
<x-btn-save :icon="false">저장</x-btn-save>
```

### 4. 버튼 타입 변경

```blade
<x-btn-save type="button">임시 저장</x-btn-save>
```

### 5. 크기 조절

```blade
<!-- 작은 버튼 -->
<x-btn-save size="btn-sm">빠른 저장</x-btn-save>

<!-- 큰 버튼 -->
<x-btn-save size="btn-lg">중요한 저장</x-btn-save>
```

### 6. 조건부 비활성화

```blade
<x-btn-save :disabled="$errors->any()">
    저장
</x-btn-save>
```

### 7. 추가 속성 사용

```blade
<x-btn-save
    id="save-config"
    data-confirm="정말 저장하시겠습니까?"
    onclick="confirmSave()"
>
    설정 저장
</x-btn-save>
```

### 8. JavaScript 이벤트 처리

```blade
<x-btn-save type="button" onclick="handleSave()">
    자동 저장
</x-btn-save>

<script>
function handleSave() {
    // 저장 로직
    console.log('저장 처리');
}
</script>
```

## 생성되는 HTML

```blade
<x-btn-save>설정 저장</x-btn-save>
```

위 코드는 다음과 같은 HTML을 생성합니다:

```html
<div class="text-end mb-4">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-circle me-1"></i>
        설정 저장
    </button>
</div>
```

## 고급 사용법

### 1. 로딩 상태 구현

```blade
<x-btn-save
    :disabled="$isProcessing"
    icon="{{ $isProcessing ? 'bi-arrow-repeat' : 'bi-check-circle' }}"
>
    {{ $isProcessing ? '저장 중...' : '설정 저장' }}
</x-btn-save>
```

### 2. 다중 저장 옵션

```blade
<div class="d-flex gap-2 justify-content-end mb-4">
    <x-btn-save type="button" size="btn-sm" icon="bi-save">
        임시 저장
    </x-btn-save>

    <x-btn-save icon="bi-check-circle">
        최종 저장
    </x-btn-save>
</div>
```

### 3. 폼 validation과 연동

```blade
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-btn-save :disabled="$errors->any()">
    {{ $errors->any() ? '오류를 수정해주세요' : '설정 저장' }}
</x-btn-save>
```

## 폼 통합 예시

```blade
<form method="POST" action="{{ route('admin.config.save') }}">
    @csrf

    <!-- 폼 필드들 -->
    <div class="mb-3">
        <label for="title" class="form-label">제목</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>

    <!-- 저장 버튼 -->
    <x-btn-save>설정 저장</x-btn-save>
</form>
```

## 스타일링 가이드

### 기본 스타일

- `btn btn-primary`: Bootstrap 기본 프라이머리 버튼
- `text-end mb-4`: 우측 정렬 및 하단 마진
- `me-1`: 아이콘과 텍스트 간격

### 커스터마이징

필요에 따라 추가 CSS 클래스를 전달할 수 있습니다:

```blade
<x-btn-save class="shadow-lg">고급 저장</x-btn-save>
```

## 접근성 (Accessibility)

- 키보드 네비게이션 지원 (Tab, Enter)
- 스크린 리더 호환
- `disabled` 상태에서 적절한 aria 속성 설정

## 아이콘 옵션

### 자주 사용되는 아이콘

```blade
<!-- 기본 저장 -->
<x-btn-save icon="bi-check-circle">저장</x-btn-save>

<!-- 파일 저장 -->
<x-btn-save icon="bi-floppy">파일 저장</x-btn-save>

<!-- 클라우드 저장 -->
<x-btn-save icon="bi-cloud-upload">클라우드 저장</x-btn-save>

<!-- 데이터베이스 저장 -->
<x-btn-save icon="bi-database">DB 저장</x-btn-save>

<!-- 설정 저장 -->
<x-btn-save icon="bi-gear">설정 저장</x-btn-save>
```

## 성능 고려사항

1. **렌더링 최적화**: 조건부 렌더링 시 불필요한 재렌더링 방지
2. **이벤트 핸들러**: onclick 이벤트보다 JavaScript 이벤트 리스너 권장
3. **아이콘 로딩**: Bootstrap Icons CDN 또는 로컬 파일 사용

## 주의사항

1. **버튼 타입**: 폼 제출용은 `type="submit"` (기본값) 사용
2. **중복 제출 방지**: JavaScript로 중복 클릭 방지 로직 구현 권장
3. **접근성**: `disabled` 상태일 때 사용자에게 이유 제공

## 관련 컴포넌트

- [`x-switch`](./x-switch.md): 토글 스위치 컴포넌트

## 트러블슈팅

### 자주 발생하는 문제

1. **아이콘이 표시되지 않음**: Bootstrap Icons가 로드되었는지 확인
2. **스타일이 적용되지 않음**: Bootstrap CSS가 로드되었는지 확인
3. **폼이 제출되지 않음**: `type="submit"`이 올바르게 설정되었는지 확인