@if($relatedPosts && $relatedPosts->count() > 0)
            <div class="row justify-content-center mt-8">
                <div class="col-xl-10 col-lg-10 col-md-12 col-12">
                    <h3 class="mb-6 text-center">관련 글</h3>
                    <div class="row">
                        @foreach($relatedPosts as $relatedPost)
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card mb-4 card-lift shadow-sm">
                                    <a href="{{ route('blog.show', $relatedPost->slug) }}">
                                        @if($relatedPost->featured_image)
                                            <img src="{{ $relatedPost->featured_image }}" class="card-img-top" alt="{{ $relatedPost->title }}" style="height: 200px; object-fit: cover;" />
                                        @else
                                            <img src="{{ asset('theme/geeks-3.3.3/dist/assets/images/blog/blogpost-' . (($loop->index % 6) + 1) . '.jpg') }}" class="card-img-top" alt="{{ $relatedPost->title }}" style="height: 200px; object-fit: cover;" />
                                        @endif
                                    </a>
                                    <div class="card-body">
                                        @if($relatedPost->category_name)
                                            <a href="{{ route('blog.category', $relatedPost->category_slug) }}"
                                               class="badge mb-2"
                                               @if($relatedPost->category_color)
                                                   style="background-color: {{ $relatedPost->category_color }}; color: white;"
                                               @else
                                                   style="background-color: #6c757d; color: white;"
                                               @endif>
                                                {{ $relatedPost->category_name }}
                                            </a>
                                        @endif
                                        <h5>
                                            <a href="{{ route('blog.show', $relatedPost->slug) }}" class="text-inherit">
                                                {{ Str::limit($relatedPost->title, 50) }}
                                            </a>
                                        </h5>
                                        @if($relatedPost->excerpt)
                                            <p class="card-text text-muted">{{ Str::limit($relatedPost->excerpt, 80) }}</p>
                                        @endif
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($relatedPost->published_at)->format('Y.m.d') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
