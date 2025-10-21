# x-help 컴포넌트

## 개요

`<x-help>` 컴포넌트는 도움말, 권장 설정, 가이드 등을 표시하는 카드형 컴포넌트입니다. 설정 페이지나 관리자 인터페이스에서 사용자에게 유용한 정보를 제공할 때 사용합니다.

## 기본 사용법

```blade
<x-help title="권장 설정" icon="bi-lightbulb" iconColor="text-warning">
    <p>여기에 도움말 내용을 작성합니다.</p>
</x-help>
```

## 속성 (Props)

| 속성 | 타입 | 기본값 | 설명 |
|------|------|--------|------|
| `title` | string | `''` | **필수** - 카드 헤더의 제목 |
| `icon` | string | `'bi-lightbulb'` | Bootstrap Icons 클래스명 |
| `iconColor` | string | `'text-warning'` | 아이콘 색상 클래스 |

## 슬롯 (Slot)

카드 내용은 `$slot`을 통해 전달됩니다. HTML, 다른 컴포넌트, 텍스트 등 자유롭게 사용할 수 있습니다.

## 사용 예시

### 1. 기본 도움말 카드

```blade
<x-help title="사용 가이드" icon="bi-info-circle" iconColor="text-info">
    <p>이 기능을 사용하기 위한 가이드입니다.</p>
    <ul>
        <li>첫 번째 단계</li>
        <li>두 번째 단계</li>
        <li>세 번째 단계</li>
    </ul>
</x-help>
```

### 2. 권장 설정 카드

```blade
<x-help title="권장 설정" icon="bi-lightbulb" iconColor="text-warning">
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

### 3. 주의사항 카드

```blade
<x-help title="주의사항" icon="bi-exclamation-triangle" iconColor="text-danger">
    <div class="alert alert-warning">
        <strong>경고:</strong> 이 설정을 변경하면 시스템에 영향을 줄 수 있습니다.
    </div>
    <p>변경하기 전에 다음 사항을 확인하세요:</p>
    <ul>
        <li>백업이 완료되었는지 확인</li>
        <li>사용자에게 사전 공지</li>
        <li>점검 시간 설정</li>
    </ul>
</x-help>
```

### 4. 다양한 아이콘과 색상

```blade
<!-- 도움말 -->
<x-help title="도움말" icon="bi-question-circle" iconColor="text-info">
    <p>자주 묻는 질문과 답변입니다.</p>
</x-help>

<!-- 팁 -->
<x-help title="유용한 팁" icon="bi-star" iconColor="text-warning">
    <p>효율적인 사용을 위한 팁을 소개합니다.</p>
</x-help>

<!-- 업데이트 정보 -->
<x-help title="최신 업데이트" icon="bi-arrow-up-circle" iconColor="text-success">
    <p>최근 추가된 기능들을 확인하세요.</p>
</x-help>

<!-- 문제 해결 -->
<x-help title="문제 해결" icon="bi-tools" iconColor="text-secondary">
    <p>문제가 발생했을 때 해결 방법입니다.</p>
</x-help>
```

## x-help-title과 함께 사용

`<x-help-title>` 컴포넌트와 함께 사용하여 구조화된 도움말을 만들 수 있습니다:

```blade
<x-help title="설정 가이드" icon="bi-gear" iconColor="text-primary">
    <x-help-title icon="bi-1-circle" iconColor="text-primary">
        1단계: 기본 설정
    </x-help-title>
    <p>먼저 기본 설정을 완료하세요.</p>

    <x-help-title icon="bi-2-circle" iconColor="text-primary">
        2단계: 고급 설정
    </x-help-title>
    <p>필요에 따라 고급 설정을 조정하세요.</p>

    <x-help-title icon="bi-3-circle" iconColor="text-primary" marginBottom="mb-0">
        3단계: 테스트
    </x-help-title>
    <p>설정이 올바르게 작동하는지 테스트하세요.</p>
</x-help>
```

## 생성되는 HTML

```blade
<x-help title="권장 설정" icon="bi-lightbulb" iconColor="text-warning">
    <p>내용</p>
</x-help>
```

위 코드는 다음과 같은 HTML을 생성합니다:

```html
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-secondary-subtle border-bottom">
        <h6 class="mb-0">
            <i class="bi-lightbulb me-2 text-warning"></i>
            권장 설정
        </h6>
    </div>
    <div class="card-body">
        <p>내용</p>
    </div>
</div>
```

## 스타일링 가이드

### 색상 체계

| 용도 | 아이콘 | 색상 클래스 |
|------|--------|-------------|
| 일반 도움말 | `bi-info-circle` | `text-info` |
| 팁/권장사항 | `bi-lightbulb` | `text-warning` |
| 성공/완료 | `bi-check-circle` | `text-success` |
| 주의/경고 | `bi-exclamation-triangle` | `text-danger` |
| 설정 | `bi-gear` | `text-primary` |

### 카드 구조

- **헤더**: 진한 회색 배경 (`bg-secondary-subtle`)
- **본문**: 흰색 배경
- **그림자**: 부드러운 그림자 효과 (`shadow-sm`)
- **여백**: 상단 여백 (`mt-4`)

## 접근성 (Accessibility)

- 의미있는 아이콘 사용으로 시각적 구분
- 명확한 제목과 구조화된 내용
- 적절한 색상 대비
- 스크린 리더 호환

## 모범 사례

### 1. 적절한 아이콘 선택

```blade
<!-- ✅ 좋은 예: 내용과 일치하는 아이콘 -->
<x-help title="보안 설정" icon="bi-shield-check" iconColor="text-success">
    보안 관련 설정 가이드
</x-help>

<!-- ❌ 나쁜 예: 내용과 맞지 않는 아이콘 -->
<x-help title="보안 설정" icon="bi-heart" iconColor="text-danger">
    보안 관련 설정 가이드
</x-help>
```

### 2. 구조화된 내용

```blade
<!-- ✅ 좋은 예: 명확한 구조 -->
<x-help title="설정 단계" icon="bi-list-ol" iconColor="text-primary">
    <x-help-title icon="bi-1-circle">1단계</x-help-title>
    <p>첫 번째 작업</p>

    <x-help-title icon="bi-2-circle">2단계</x-help-title>
    <p>두 번째 작업</p>
</x-help>
```

### 3. 적절한 길이

도움말 내용은 간결하고 이해하기 쉽게 작성하되, 필요한 정보는 빠뜨리지 않도록 합니다.

## 주의사항

1. **제목 필수**: `title` 속성은 반드시 제공해야 합니다.
2. **아이콘 일관성**: 비슷한 용도의 카드는 동일한 아이콘 사용 권장
3. **색상 의미**: 색상이 가지는 의미와 내용이 일치하도록 주의
4. **길이 조절**: 너무 긴 내용은 사용자 경험을 해칠 수 있음

## 관련 컴포넌트

- [`x-help-title`](./x-help-title.md): 도움말 내 제목 컴포넌트
- [`x-switch`](./x-switch.md): 토글 스위치 컴포넌트
- [`x-input-number`](./x-input-number.md): 숫자 입력 컴포넌트
- [`x-btn-save`](./x-btn-save.md): 저장 버튼 컴포넌트