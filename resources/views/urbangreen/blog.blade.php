@extends('urbangreen.layouts.main')

@section('content')
<!-- ##### Breadcrumb Area Start ##### -->
<div class="breadcrumb-area">
    <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
        <h2>Manage Statutes</h2>
    </div>
</div>
<!-- ##### Breadcrumb Area End ##### -->

<section class="alazea-blog-area mb-100">
    <div class="container">
        <!-- Profanity Error Alert (Top of Page) -->
        @if($errors->has('profanity'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fa fa-exclamation-triangle"></i> Inappropriate Content Detected!</strong>
                        <p class="mb-0">{{ $errors->first('profanity') }}</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-12">
                <form action="{{ route('front.blog') }}" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search statutes by title or description..." 
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn alazea-btn" type="submit">
                                <i class="fa fa-search"></i> Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('front.blog') }}" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Statute Button + Results Info -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        @if(request('search'))
                            Search Results for "{{ request('search') }}"
                        @else
                            Latest Statutes
                        @endif
                    </h4>
                    @if(request('search'))
                        <small class="text-muted">Found {{ $statutes->total() }} result(s)</small>
                    @endif
                </div>
                <button class="btn alazea-btn" data-toggle="modal" data-target="#addStatuteModal">
                    <i class="fa fa-plus mr-1"></i> Add Statute
                </button>
            </div>
        </div>

        <!-- Modal: Add Statute -->
        <div class="modal fade" id="addStatuteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Statute</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($errors->has('profanity'))
                            <div class="alert alert-danger">
                                <h5 class="alert-heading"><i class="fa fa-ban"></i> Inappropriate Language Detected!</h5>
                                <p class="mb-0">{{ $errors->first('profanity') }}</p>
                                <hr>
                                <p class="mb-0 small">Please remove any offensive words and try again.</p>
                            </div>
                        @elseif ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="addStatuteForm" action="{{ route('statutes.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="titre">Title</label>
                                <input type="text" name="titre" id="titre" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" rows="4" class="form-control" required></textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label for="photo">Photo</label>
                                <input type="file" name="photo" id="photo" class="form-control">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn alazea-btn" form="addStatuteForm">Add Statute</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statute List -->
        <div class="row mt-2">
            @forelse($statutes as $statute)
                <div class="col-12 col-lg-6">
                    <div class="single-blog-post mb-50">
                        <div class="post-thumbnail mb-30">
                            @if($statute->photo)
                                <img src="{{ asset($statute->photo) }}" alt="{{ $statute->titre }}">
                            @else
                                <img src="{{ asset('urbangreen/img/bg-img/6.jpg') }}" alt="">
                            @endif
                        </div>

                        <div class="post-content">
                            <h5>{{ $statute->titre }}</h5>
                            <div class="post-meta">
                                <a href="#"><i class="fa fa-clock-o"></i> {{ $statute->created_at->format('d M Y') }}</a>
                                <a href="#"><i class="fa fa-user"></i> Admin</a>
                            </div>
                            <p class="post-excerpt">{{ Str::limit($statute->description, 100) }}</p>

                            <!-- Comments Section -->
                            <div class="post-comments mt-3">
                                <h6>Comments:</h6>

                                @foreach($statute->comentes as $comente)
                                    <div class="single-comment mb-2 p-2 border rounded">
                                        <p class="mb-1">{{ $comente->description }}</p>
                                        <small class="text-muted">{{ $comente->created_at->diffForHumans() }}</small>

                                        <!-- Delete button -->
                                        <form action="{{ route('comentes.destroy', $comente->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                @endforeach

                                <!-- Add Comment Form -->
                                <form action="{{ route('comentes.store', $statute->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    <div class="form-group">
                                        <textarea name="description" class="form-control mb-2" rows="2" placeholder="Write a comment..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Comment</button>
                                </form>
                            </div>
                            <!-- Reaction buttons -->
                            <div class="post-reactions mt-3 d-flex align-items-center">
                                @auth
                                    <form action="{{ route('statutes.reaction', $statute->id) }}" method="POST" class="me-2">
                                        @csrf
                                        <input type="hidden" name="reaction" value="like">
                                        <button type="submit" class="btn btn-light" title="Like">👍 <span class="ms-1">{{ $statute->likesCount() }}</span></button>
                                    </form>

                                    <form action="{{ route('statutes.reaction', $statute->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="reaction" value="dislike">
                                        <button type="submit" class="btn btn-light" title="Dislike">👎 <span class="ms-1">{{ $statute->dislikesCount() }}</span></button>
                                    </form>
                                @else
                                    <div class="text-muted">Log in to react to this statute.</div>
                                @endauth
                            </div>
                            <!-- End Comments Section -->

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p>No statutes found.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="row">
            <div class="col-12">
                <nav aria-label="Page navigation">
                    {{ $statutes->links('pagination::bootstrap-4') }}
                </nav>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Automatically reopen modal if there are form errors (including profanity)
    @if($errors->any())
        $(document).ready(function() {
           
            
            // Show browser alert for profanity errors
            @if($errors->has('profanity'))
                alert('⚠️ INAPPROPRIATE CONTENT DETECTED!\n\n{{ $errors->first('profanity') }}\n\nPlease remove offensive words and try again.');
            @endif
        });
    @endif
</script>
@endpush

@endsection
