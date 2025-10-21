# x-help-title 컴포넌트

## 개요

`<x-help-title>` 컴포넌트는 `<x-help>` 컴포넌트 내부에서 섹션 제목을 표시하는 컴포넌트입니다. 도움말 카드 내에서 구조화된 내용을 만들 때 사용하여 가독성을 높입니다.

## 기본 사용법

```blade
<x-help-title icon="bi-1-circle" iconColor="text-primary">
    1단계: 기본 설정
</x-help-title>
```

## 속성 (Props)

| 속성 | 타입 | 기본값 | 설명 |
|------|------|--------|------|
| `icon` | string | `''` | Bootstrap Icons 클래스명 (선택사항) |
| `iconColor` | string | `'text-primary'` | 아이콘 및 제목 색상 클래스 |
| `marginBottom` | string | `'mb-3'` | 하단 여백 클래스 |

## 슬롯 (Slot)

제목 텍스트는 `$slot`을 통해 전달됩니다. 간단한 텍스트나 HTML을 포함할 수 있습니다.

## 사용 예시

### 1. 아이콘 있는 제목

```blade
<x-help-title icon="bi-shield-check" iconColor="text-success">
    보안 중심형
</x-help-title>
```

### 2. 아이콘 없는 제목

```blade
<x-help-title iconColor="text-warning">
    주의사항
</x-help-title>
```

### 3. 여백 조정

```blade
<x-help-title icon="bi-info-circle" marginBottom="mb-0">
    마지막 섹션
</x-help-title>
```

### 4. 단계별 가이드

```blade
<x-help title="설정 가이드" icon="bi-gear">
    <x-help-title icon="bi-1-circle" iconColor="text-primary">
        1단계: 초기 설정
    </x-help-title>
    <p>첫 번째 설정을 완료하세요.</p>

    <x-help-title icon="bi-2-circle" iconColor="text-primary">
        2단계: 세부 조정
    </x-help-title>
    <p>세부 옵션을 설정하세요.</p>

    <x-help-title icon="bi-3-circle" iconColor="text-primary" marginBottom="mb-0">
        3단계: 완료
    </x-help-title>
    <p>설정을 저장하고 테스트하세요.</p>
</x-help>
```

### 5. 카테고리별 구분

```blade
<x-help title="권장 설정" icon="bi-lightbulb">
    <x-help-title icon="bi-shield-check" iconColor="text-success">
        보안 중심형
    </x-help-title>
    <ul class="small text-muted">
        <li>관리자만 작성 허용</li>
        <li>사용자 글 승인 필요</li>
    </ul>

    <x-help-title icon="bi-people" iconColor="text-primary">
        커뮤니티형
    </x-help-title>
    <ul class="small text-muted">
        <li>회원 작성 + 선택적 승인</li>
        <li>투표 및 태그 기능 활성화</li>
    </ul>
</x-help>
```

## 생성되는 HTML

```blade
<x-help-title icon="bi-check-circle" iconColor="text-success">
    완료됨
</x-help-title>
```

위 코드는 다음과 같은 HTML을 생성합니다:

```html
<div class="mb-3">
    <h6 class="text-success small">
        <i class="bi-check-circle me-1"></i>
        완료됨
    </h6>
</div>
```

## 스타일링 가이드

### 권장 색상 조합

| 용도 | 아이콘 | 색상 클래스 |
|------|--------|-------------|
| 성공/완료 | `bi-check-circle` | `text-success` |
| 정보/안내 | `bi-info-circle` | `text-info` |
| 주의/경고 | `bi-exclamation-triangle` | `text-warning` |
| 중요/오류 | `bi-x-circle` | `text-danger` |
| 일반/기본 | `bi-circle` | `text-primary` |
| 숫자 단계 | `bi-1-circle`, `bi-2-circle` | `text-primary` |

### 아이콘 선택 가이드

#### 단계별 아이콘
```blade
<x-help-title icon="bi-1-circle">1단계</x-help-title>
<x-help-title icon="bi-2-circle">2단계</x-help-title>
<x-help-title icon="bi-3-circle">3단계</x-help-title>
```

#### 상태별 아이콘
```blade
<x-help-title icon="bi-check-circle" iconColor="text-success">완료</x-help-title>
<x-help-title icon="bi-clock" iconColor="text-warning">진행중</x-help-title>
<x-help-title icon="bi-x-circle" iconColor="text-danger">실패</x-help-title>
```

#### 카테고리별 아이콘
```blade
<x-help-title icon="bi-shield-check">보안</x-help-title>
<x-help-title icon="bi-people">사용자</x-help-title>
<x-help-title icon="bi-gear">설정</x-help-title>
```

## x-help와 함께 사용

`<x-help-title>`은 주로 `<x-help>` 컴포넌트 내부에서 사용됩니다:

```blade
<x-help title="포럼 설정 가이드" icon="bi-forum">
    <x-help-title icon="bi-gear" iconColor="text-primary">
        기본 설정
    </x-help-title>
    <p>포럼의 기본 동작을 설정합니다.</p>

    <x-help-title icon="bi-shield" iconColor="text-warning">
        권한 설정
    </x-help-title>
    <p>사용자 권한을 관리합니다.</p>

    <x-help-title icon="bi-eye" iconColor="text-info" marginBottom="mb-0">
        표시 설정
    </x-help-title>
    <p>화면에 표시되는 내용을 조정합니다.</p>
</x-help>
```

## 접근성 (Accessibility)

- 적절한 헤딩 레벨 사용 (`h6`)
- 의미있는 아이콘으로 시각적 구분
- 색상만으로 정보를 전달하지 않음
- 스크린 리더 호환

## 모범 사례

### 1. 일관된 아이콘 사용

```blade
<!-- ✅ 좋은 예: 일관된 단계 표시 -->
<x-help-title icon="bi-1-circle" iconColor="text-primary">1단계</x-help-title>
<x-help-title icon="bi-2-circle" iconColor="text-primary">2단계</x-help-title>
<x-help-title icon="bi-3-circle" iconColor="text-primary">3단계</x-help-title>

<!-- ❌ 나쁜 예: 혼재된 아이콘 스타일 -->
<x-help-title icon="bi-1-circle">1단계</x-help-title>
<x-help-title icon="bi-arrow-right">2단계</x-help-title>
<x-help-title icon="bi-check">3단계</x-help-title>
```

### 2. 적절한 색상 선택

```blade
<!-- ✅ 좋은 예: 의미에 맞는 색상 -->
<x-help-title icon="bi-check-circle" iconColor="text-success">완료됨</x-help-title>
<x-help-title icon="bi-exclamation-triangle" iconColor="text-warning">주의</x-help-title>

<!-- ❌ 나쁜 예: 의미와 맞지 않는 색상 -->
<x-help-title icon="bi-check-circle" iconColor="text-danger">완료됨</x-help-title>
<x-help-title icon="bi-exclamation-triangle" iconColor="text-success">주의</x-help-title>
```

### 3. 마지막 요소의 여백 제거

```blade
<!-- ✅ 좋은 예: 마지막 요소는 하단 여백 제거 -->
<x-help-title icon="bi-info">정보 1</x-help-title>
<p>내용 1</p>

<x-help-title icon="bi-info" marginBottom="mb-0">정보 2</x-help-title>
<p>내용 2</p>
```

## 주의사항

1. **아이콘 선택사항**: `icon` 속성은 선택사항이며, 제공하지 않으면 아이콘 없이 제목만 표시됩니다.
2. **색상 일관성**: 같은 종류의 정보는 동일한 색상 사용을 권장합니다.
3. **여백 조정**: 마지막 섹션에서는 `marginBottom="mb-0"`으로 불필요한 여백을 제거하세요.
4. **제목 길이**: 제목은 간결하고 명확하게 작성하세요.

## 관련 컴포넌트

- [`x-help`](./x-help.md): 메인 도움말 카드 컴포넌트
- [`x-switch`](./x-switch.md): 토글 스위치 컴포넌트
- [`x-input-number`](./x-input-number.md): 숫자 입력 컴포넌트
- [`x-btn-save`](./x-btn-save.md): 저장 버튼 컴포넌트