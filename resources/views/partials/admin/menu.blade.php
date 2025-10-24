<!-- 게시물 (Board) 관리 -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navBoard" aria-expanded="false"
        aria-controls="navBoard">
        <i class="nav-icon fe fe-message-square me-2"></i>
        게시물 관리
    </a>
    <div id="navBoard" class="collapse" data-bs-parent="#sideNavbar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.board.dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    대시보드
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.board.list.index') }}">
                    <i class="bi bi-layout-text-sidebar me-2"></i>
                    게시판 목록
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.board.related.index') }}">
                    <i class="bi bi-link-45deg me-2"></i>
                    관련글
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.board.trend.index') }}">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    트렌드글
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.board.comment.reports.index') }}">
                    <i class="bi bi-flag me-2"></i>
                    댓글 신고 관리
                </a>
            </li>
        </ul>
    </div>
</li>

<!-- 포럼 (Forum) 관리 -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navForum" aria-expanded="false"
        aria-controls="navForum">
        <i class="nav-icon bi bi-chat-square-text me-2"></i>
        포럼 관리
    </a>
    <div id="navForum" class="collapse" data-bs-parent="#sideNavbar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.forum.index') }}">
                    <i class="bi bi-chat-square-text me-2"></i>
                    포럼 글
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.forum.category.index') }}">
                    <i class="bi bi-tags me-2"></i>
                    포럼 카테고리
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.forum.config.index') }}">
                    <i class="bi bi-gear me-2"></i>
                    포럼 설정
                </a>
            </li>
        </ul>
    </div>
</li>

<!-- 블로그 (Blog) 관리 -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navBlog" aria-expanded="false"
        aria-controls="navBlog">
        <i class="nav-icon bi bi-journal-text me-2"></i>
        블로그 관리
    </a>
    <div id="navBlog" class="collapse" data-bs-parent="#sideNavbar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.blog.index') }}">
                    <i class="bi bi-journal-text me-2"></i>
                    블로그 글
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.blog.category.index') }}">
                    <i class="bi bi-bookmark-star me-2"></i>
                    블로그 카테고리
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cms.blog.config.index') }}">
                    <i class="bi bi-gear me-2"></i>
                    블로그 설정
                </a>
            </li>
        </ul>
    </div>
</li>
