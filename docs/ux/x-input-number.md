# x-input-number 컴포넌트

## 개요

`<x-input-number>` 컴포넌트는 숫자 입력을 위한 일관된 폼 요소입니다. 라벨, 입력 필드, 설명을 하나의 컴포넌트로 통합하여 일관된 UX를 제공하며, min/max 값 제한, step 설정 등 숫자 입력에 필요한 기능을 지원합니다.

## 기본 사용법

```blade
<x-input-number name="max_items" :value="10" :min="1" :max="100">
    <strong>최대 아이템 수</strong>
    <div class="text-muted small">설정할 수 있는 최대 아이템 개수입니다.</div>
</x-input-number>
```

## 속성 (Props)

| 속성 | 타입 | 기본값 | 설명 |
|------|------|--------|------|
| `name` | string | `''` | **필수** - input의 name 속성 |
| `value` | string/number | `''` | 입력 필드의 초기값 |
| `min` | number | `null` | 최솟값 제한 |
| `max` | number | `null` | 최댓값 제한 |
| `step` | number | `1` | 증감 단위 |
| `required` | boolean | `false` | 필수 입력 여부 |
| `disabled` | boolean | `false` | 비활성화 상태 |
| `placeholder` | string | `''` | 플레이스홀더 텍스트 |

## 슬롯 (Slot)

컴포넌트의 내용은 `$slot`을 통해 전달됩니다. 라벨과 설명을 포함한 모든 내용을 자유롭게 구성할 수 있습니다.

```blade
<x-input-number name="example" :value="5">
    <strong>라벨 텍스트</strong>
    <div class="text-muted small">설명 텍스트</div>
</x-input-number>
```

## 사용 예시

### 1. 기본 숫자 입력

```blade
<x-input-number
    name="max_images_per_post"
    :value="$config['settings']['max_images_per_post']"
    :min="1"
    :max="50"
>
    <strong>글당 최대 이미지 수</strong>
    <div class="text-muted small">하나의 글에 첨부할 수 있는 최대 이미지 수입니다.</div>
</x-input-number>
```

### 2. 소수점 입력 (step 사용)

```blade
<x-input-number
    name="price"
    :value="$product->price"
    :min="0"
    :step="0.01"
    placeholder="0.00"
>
    <strong>상품 가격</strong>
    <div class="text-muted small">상품의 판매 가격을 입력하세요.</div>
</x-input-number>
```

### 3. 필수 입력 필드

```blade
<x-input-number
    name="quantity"
    :value="old('quantity', 1)"
    :min="1"
    :required="true"
>
    <strong>주문 수량 <span class="text-danger">*</span></strong>
    <div class="text-muted small">주문할 상품의 수량을 입력하세요.</div>
</x-input-number>
```

### 4. 조건부 비활성화

```blade
<x-input-number
    name="max_users"
    :value="$subscription->max_users"
    :min="1"
    :max="1000"
    :disabled="!$user->canModifySubscription()"
>
    <strong>최대 사용자 수</strong>
    <div class="text-muted small">구독 플랜에서 허용하는 최대 사용자 수입니다.</div>
</x-input-number>
```

### 5. 추가 속성 사용

```blade
<x-input-number
    name="timeout"
    :value="30"
    :min="5"
    :max="300"
    :step="5"
    data-toggle="tooltip"
    title="초 단위로 입력"
>
    <strong>타임아웃 설정</strong>
    <div class="text-muted small">연결 타임아웃 시간을 설정합니다 (5-300초).</div>
</x-input-number>
```

### 6. 범위별 그룹화

```blade
<!-- 작은 값 범위 -->
<x-input-number name="max_tags" :value="5" :min="1" :max="10">
    <strong>최대 태그 수</strong>
    <div class="text-muted small">글당 최대 태그 개수 (1-10개)</div>
</x-input-number>

<!-- 중간 값 범위 -->
<x-input-number name="pagination_limit" :value="15" :min="5" :max="50">
    <strong>페이지당 게시글 수</strong>
    <div class="text-muted small">한 페이지에 표시할 게시글 개수 (5-50개)</div>
</x-input-number>

<!-- 큰 값 범위 -->
<x-input-number name="auto_excerpt_length" :value="150" :min="50" :max="500">
    <strong>자동 요약 길이</strong>
    <div class="text-muted small">요약 글자 수 (50-500자)</div>
</x-input-number>
```

## 생성되는 HTML

```blade
<x-input-number name="max_items" :value="10" :min="1" :max="100">
    <strong>최대 아이템 수</strong>
    <div class="text-muted small">설정할 수 있는 최대 아이템 개수입니다.</div>
</x-input-number>
```

위 코드는 다음과 같은 HTML을 생성합니다:

```html
<div class="mb-3">
    <label for="max_items" class="form-label">
        <strong>최대 아이템 수</strong>
        <div class="text-muted small">설정할 수 있는 최대 아이템 개수입니다.</div>
    </label>
    <input
        type="number"
        class="form-control"
        id="max_items"
        name="max_items"
        value="10"
        min="1"
        max="100"
        step="1"
    >
</div>
```

## 폼 처리

### 컨트롤러에서 처리

```php
// AdminConfigUpdate.php
public function __invoke(Request $request)
{
    $request->validate([
        'max_images_per_post' => 'required|integer|min:1|max:50',
        'auto_excerpt_length' => 'required|integer|min:50|max:500',
        'pagination_limit' => 'required|integer|min:5|max:50',
    ]);

    $settings = [
        'max_images_per_post' => (int)$request->input('max_images_per_post'),
        'auto_excerpt_length' => (int)$request->input('auto_excerpt_length'),
        'pagination_limit' => (int)$request->input('pagination_limit'),
    ];

    // 설정 저장 로직
}
```

### Validation Rules 예시

```php
$rules = [
    // 기본 정수 검증
    'quantity' => 'required|integer|min:1',

    // 범위 제한
    'max_users' => 'required|integer|min:1|max:1000',

    // 소수점 허용
    'price' => 'required|numeric|min:0|max:999999.99',

    // 선택적 입력
    'timeout' => 'nullable|integer|min:5|max:300',
];
```

## 스타일링 가이드

### 권장 구조

```blade
<x-input-number name="field_name" :value="$value" :min="1" :max="100">
    <strong>주요 라벨</strong>
    <div class="text-muted small">부가 설명 및 제한사항</div>
</x-input-number>
```

### CSS 클래스

- `.form-control`: Bootstrap의 기본 input 스타일
- `.form-label`: 라벨 스타일
- `.text-muted.small`: 설명 텍스트용 스타일
- `.mb-3`: 컴포넌트 간 간격

## 접근성 (Accessibility)

- `label`과 `input`이 올바르게 연결됨 (`for` 속성 사용)
- 키보드 네비게이션 지원 (Tab, 화살표 키)
- 스크린 리더 호환
- `min`, `max` 속성으로 값 범위 명시

## 모범 사례

### 1. 값 범위 설정

```blade
<!-- ✅ 좋은 예: 명확한 범위 설정 -->
<x-input-number name="max_tags" :value="5" :min="1" :max="10">
    <strong>최대 태그 수</strong>
    <div class="text-muted small">글당 최대 태그 개수 (1-10개)</div>
</x-input-number>

<!-- ❌ 나쁜 예: 범위가 너무 넓거나 없음 -->
<x-input-number name="max_tags" :value="5">
    <strong>최대 태그 수</strong>
</x-input-number>
```

### 2. 의미있는 step 값

```blade
<!-- ✅ 좋은 예: 의미있는 증감 단위 -->
<x-input-number name="timeout" :value="30" :min="5" :max="300" :step="5">
    <strong>타임아웃 (초)</strong>
    <div class="text-muted small">5초 단위로 설정 가능</div>
</x-input-number>

<!-- ✅ 좋은 예: 소수점 가격 -->
<x-input-number name="price" :value="10.99" :min="0" :step="0.01">
    <strong>상품 가격</strong>
    <div class="text-muted small">센트 단위까지 입력 가능</div>
</x-input-number>
```

### 3. 설명 텍스트 활용

```blade
<!-- ✅ 좋은 예: 명확한 설명과 제한사항 -->
<x-input-number name="file_size" :value="5" :min="1" :max="20">
    <strong>최대 파일 크기 (MB)</strong>
    <div class="text-muted small">업로드할 수 있는 파일의 최대 크기입니다. (1-20MB)</div>
</x-input-number>
```

## 주의사항

1. **name 속성 필수**: 폼 전송을 위해 반드시 설정해야 합니다.
2. **값 타입**: value는 문자열이나 숫자 모두 가능하지만 일관성 유지 권장
3. **범위 검증**: min/max 속성과 서버 validation이 일치해야 합니다.
4. **접근성**: 시각적 제약이 있는 사용자를 위해 명확한 라벨 제공

## 관련 컴포넌트

- [`x-switch`](./x-switch.md): 토글 스위치 컴포넌트
- [`x-btn-save`](./x-btn-save.md): 폼 저장 버튼 컴포넌트

## 확장 아이디어

### 향후 개선 가능한 기능

1. **단위 표시**: 컴포넌트에 단위(MB, 초, 개 등) 자동 표시
2. **실시간 계산**: 입력값에 따른 실시간 계산 결과 표시
3. **범위 가이드**: 슬라이더나 진행 바로 현재 값의 범위 내 위치 표시
4. **입력 도움말**: 툴팁이나 모달로 상세한 설명 제공