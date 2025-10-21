# x-switch 컴포넌트

## 개요

`<x-switch>` 컴포넌트는 Bootstrap의 form-switch를 기반으로 한 토글 스위치 컴포넌트입니다. 설정 페이지나 폼에서 boolean 값을 입력받을 때 사용하며, 일관된 스타일과 동작을 제공합니다.

## 기본 사용법

```blade
<x-switch name="enable_feature" :checked="true">
    <strong>기능 활성화</strong>
    <div class="text-muted small">이 기능을 활성화합니다.</div>
</x-switch>
```

## 속성 (Props)

| 속성 | 타입 | 기본값 | 설명 |
|------|------|--------|------|
| `name` | string | `''` | **필수** - input의 name 속성 |
| `checked` | boolean | `false` | 스위치의 체크 상태 |
| `value` | string | `'1'` | 체크되었을 때의 값 |
| `hiddenValue` | string | `'0'` | 체크되지 않았을 때의 값 |

## 슬롯 (Slot)

컴포넌트의 내용은 `$slot`을 통해 전달됩니다. 이를 통해 라벨과 설명을 자유롭게 구성할 수 있습니다.

```blade
<x-switch name="example" :checked="false">
    <!-- 여기에 라벨과 설명을 작성 -->
    <strong>라벨 텍스트</strong>
    <div class="text-muted small">설명 텍스트</div>
</x-switch>
```

## 사용 예시

### 1. 기본 스위치

```blade
<x-switch name="admin_write" :checked="$config['policies']['admin_write']['enabled']">
    <strong>관리자 작성 허용</strong>
    <div class="text-muted small">시스템 관리자가 블로그를 작성할 수 있습니다.</div>
</x-switch>
```

### 2. 동적 체크 상태

```blade
<x-switch name="notifications" :checked="$user->preferences['notifications']">
    <strong>알림 수신</strong>
    <div class="text-muted small">새로운 메시지나 업데이트 알림을 받습니다.</div>
</x-switch>
```

### 3. 고급 스타일링

```blade
<x-switch name="premium_feature" :checked="$user->isPremium()">
    <strong>
        <i class="bi bi-star-fill text-warning me-1"></i>
        프리미엄 기능
        <span class="badge bg-primary ms-2">NEW</span>
    </strong>
    <div class="text-muted small">
        프리미엄 사용자만 이용할 수 있는 고급 기능입니다.
        <a href="/upgrade" class="text-decoration-none">업그레이드 →</a>
    </div>
</x-switch>
```

### 4. 추가 속성 사용

```blade
<x-switch
    name="auto_save"
    :checked="$settings['auto_save']"
    data-toggle="tooltip"
    title="자동 저장 설정"
>
    <strong>자동 저장</strong>
    <div class="text-muted small">작업 내용을 자동으로 저장합니다.</div>
</x-switch>
```

## 폼 처리

컴포넌트는 자동으로 hidden input을 생성하여 체크되지 않은 상태에서도 값을 전송합니다:

```html
<!-- 생성되는 HTML -->
<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="enable_feature" value="0">
        <input class="form-check-input" type="checkbox" id="enable_feature" name="enable_feature" value="1" checked>
        <label class="form-check-label" for="enable_feature">
            <!-- slot 내용 -->
        </label>
    </div>
</div>
```

## 컨트롤러에서 처리

```php
// AdminConfigUpdate.php
public function __invoke(Request $request)
{
    $settings = [
        'enable_feature' => $request->input('enable_feature') === '1',
        // 또는
        'enable_feature' => $request->input('enable_feature') === '1' || $request->input('enable_feature') === 'on',
    ];
}
```

## 스타일링 가이드

### 권장 구조

```blade
<x-switch name="setting_name" :checked="$value">
    <strong>주요 라벨</strong>
    <div class="text-muted small">부가 설명</div>
</x-switch>
```

### CSS 클래스

- `.form-check.form-switch`: Bootstrap의 기본 스위치 스타일
- `.text-muted.small`: 설명 텍스트용 스타일
- 추가 Bootstrap 유틸리티 클래스 사용 가능

## 접근성 (Accessibility)

- `label`과 `input`이 올바르게 연결됨 (`for` 속성 사용)
- 키보드 네비게이션 지원
- 스크린 리더 호환

## 주의사항

1. **name 속성 필수**: 폼 전송을 위해 반드시 설정해야 합니다.
2. **checked 속성**: boolean 값으로 전달해야 합니다.
3. **슬롯 내용**: HTML 태그를 자유롭게 사용할 수 있지만, 시맨틱한 구조를 유지하세요.

## 관련 컴포넌트

- [`x-btn-save`](./x-btn-save.md): 폼 저장 버튼 컴포넌트