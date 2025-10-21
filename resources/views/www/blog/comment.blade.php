@if ($post->allow_comments)
    <div class="row justify-content-center mt-8">
        <div>
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots me-2 text-primary"></i>
                        댓글 ({{ count($commentsTree) }})
                    </h4>
                </div>
                <div class="card-body">
                    @if (count($commentsTree) > 0)
                        <div id="comments-list" class="mb-4">
                            @each('jiny-post::www.blog.partials.comment', $commentsTree, 'comment')
                        </div>
                    @endif

                    <!-- Comment Form -->
                    <div id="comment-form-section">
                        <h5 class="mb-4" id="comment-form-title">댓글 작성</h5>

                        <form id="commentForm" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="blog_id" value="{{ $post->id }}">
                            <input type="hidden" name="parent_id" id="parent_id" value="">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="author_name" class="form-label">이름 <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="author_name"
                                        name="author_name" required>
                                    <div class="invalid-feedback">이름을 입력해주세요.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="author_email" class="form-label">이메일 <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="author_email"
                                        name="author_email" required>
                                    <div class="invalid-feedback">올바른 이메일을 입력해주세요.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="author_website" class="form-label">웹사이트</label>
                                <input type="url" class="form-control" id="author_website"
                                    name="author_website" placeholder="https://example.com">
                                <div class="invalid-feedback">올바른 URL을 입력해주세요.</div>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">댓글 내용 <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="5" required placeholder="댓글을 작성해주세요..."></textarea>
                                <div class="invalid-feedback">댓글 내용을 입력해주세요.</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    댓글은 관리자 승인 후 표시됩니다.
                                </small>
                                <button type="submit" class="btn btn-primary" id="submitComment">
                                    <i class="bi bi-send me-2"></i>댓글 등록
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Comments Disabled Message -->
    <div class="row justify-content-center mt-8">
        <div class="col-xl-8 col-lg-8 col-md-12 col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-chat-slash fs-1 d-block mb-3"></i>
                    <h5 class="text-muted">댓글이 비활성화되어 있습니다</h5>
                    <p class="mb-0">이 글에는 댓글을 작성할 수 없습니다.</p>
                </div>
            </div>
        </div>
    </div>
@endif
