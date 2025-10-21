<table class="table table-hover mb-0">
    <thead class="table-light">
        <tr>
            <th style="width: 80px;">번호</th>
            <th>제목</th>
            <th style="width: 120px;">카테고리</th>
            <th style="width: 100px;">조회수</th>
            <th style="width: 100px;">좋아요</th>
            <th style="width: 200px;">작성일</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td class="text-center">{{ $row->id }}</td>
                <td>
                    <a href="{{ route('forum.show', $row->id) }}" class="text-decoration-none text-dark">
                        {{ $row->title }}
                    </a>
                    @if ($row->image)
                        <i class="bi bi-image text-muted ms-1" title="이미지 포함"></i>
                    @endif
                    @if ($row->tags)
                        <div class="mt-1">
                            @foreach (explode(',', $row->tags) as $tag)
                                <span class="badge bg-light text-dark me-1">#{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td>
                    @if ($row->categories)
                        @foreach (explode(',', $row->categories) as $category)
                            <span class="badge bg-secondary me-1">{{ trim($category) }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format($row->click ?? 0) }}</td>
                <td class="text-center">
                    @if ($row->like > 0)
                        <span class="text-danger">
                            <i class="bi bi-heart-fill"></i>
                            {{ number_format($row->like) }}
                        </span>
                    @else
                        <span class="text-muted">
                            <i class="bi bi-heart"></i>
                            0
                        </span>
                    @endif
                </td>
                <td class="text-muted">
                    <div>{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i') }}</div>
                    <div class="small text-secondary">{{ $row->name ?? '익명' }}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    @if ($currentSearch || $currentCategory)
                        검색 결과가 없습니다.
                    @else
                        포럼 글이 없습니다.
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
