@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h4>Sales Representative Ratings</h4>
                <p class="text-muted small">Evaluate the performance of field staff assigned to your network.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($fieldStaff as $staff)
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="avatar-wrapper">
                            <img src="{{ $staff->user->avatar_url }}" class="rounded-circle border border-2 border-white shadow-sm" width="60" height="60" alt="{{ $staff->user->name }}">
                        </div>
                        <div>
                            <h6 class="fw-800 mb-0">{{ $staff->user->name }}</h6>
                            <span class="badge bg-soft-primary text-primary px-2 py-1 small rounded-pill">Field Staff</span>
                            <div class="text-muted small mt-1"><i class="fa fa-phone me-1"></i> {{ $staff->user->contact_no ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="rating-section">
                        @php
                            $ratingRecord = $staff->ratings->first(); // Get the first rating as the general rating
                            $currentRating = $ratingRecord ? $ratingRecord->rating : 0;
                        @endphp
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-dark">Performance Rating</span>
                            <span class="rating-value small text-muted">{{ $currentRating > 0 ? $currentRating . '/5' : 'Not Rated' }}</span>
                        </div>
                        
                        <div class="star-rating mb-4" data-staff-id="{{ $staff->id }}" data-category="general">
                            @for($i = 1; $i <= 5; $i++)
                            <i class="fa fa-star star {{ $i <= $currentRating ? 'active' : '' }}" data-value="{{ $i }}" style="font-size: 1.5rem;"></i>
                            @endfor
                        </div>

                        <div class="feedback-display mb-3">
                            <p class="text-muted small italic mb-0" id="feedback-text-{{ $staff->id }}">
                                {{ $ratingRecord && $ratingRecord->comments ? '"' . $ratingRecord->comments . '"' : 'No feedback provided yet.' }}
                            </p>
                        </div>

                        <button class="btn btn-primary btn-sm w-100 rounded-pill py-2 fw-bold" onclick="openCommentModal({{ $staff->id }}, '{{ $staff->user->name }}', '{{ $ratingRecord->comments ?? '' }}')">
                            <i class="fa fa-comment me-2"></i> {{ $ratingRecord ? 'Update Feedback' : 'Add Feedback' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                <div class="icon-circle bg-soft-light mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fa fa-users fs-1 text-muted"></i>
                </div>
                <h5>No Field Staff Assigned</h5>
                <p class="text-muted">Once field staff are assigned to your orders or retailers, they will appear here for rating.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-800">Feedback for <span id="staffName"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modalStaffId">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Detailed Comments</label>
                    <textarea id="staffComments" class="form-control rounded-3" rows="4" placeholder="Write your feedback here..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveComments()">Save Feedback</button>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    .star-rating {
        display: flex;
        gap: 4px;
    }
    .star-rating .star {
        font-size: 1.2rem;
        color: #e4e4e4;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .star-rating .star.active,
    .star-rating .star:hover {
        color: #ffc107;
    }
    .bg-soft-primary { background-color: rgba(var(--bs-primary-rgb), 0.1); }
    .bg-soft-light { background-color: rgba(0,0,0,0.03); }
    .fw-800 { font-weight: 800; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.star-rating .star').on('click', function() {
        const value = $(this).data('value');
        const container = $(this).parent();
        const staffId = container.data('staff-id');
        const category = container.data('category');
        
        // Update UI
        container.find('.star').removeClass('active');
        container.find('.star').each(function(index) {
            if (index < value) $(this).addClass('active');
        });
        container.closest('.category-item').find('.rating-value').text(value + '/5');

        // Save to DB
        $.ajax({
            url: "{{ route('distributor.staff-ratings.store') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                field_staff_id: staffId,
                rating: value,
                category: 'general'
            },
            success: function(response) {
                showToast('success', 'Rating updated!');
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.error || 'Something went wrong');
            }
        });
    });
});

function openCommentModal(id, name, existingComment) {
    $('#modalStaffId').val(id);
    $('#staffName').text(name);
    $('#staffComments').val(existingComment);
    $('#commentModal').modal('show');
}

function saveComments() {
    const id = $('#modalStaffId').val();
    const comments = $('#staffComments').val();
    
    // Get the current star rating for this staff
    const currentRating = $(`.star-rating[data-staff-id="${id}"] .star.active`).length || 5;

    $.ajax({
        url: "{{ route('distributor.staff-ratings.store') }}",
        method: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            field_staff_id: id,
            rating: currentRating,
            category: 'general',
            comments: comments
        },
        success: function(response) {
            $('#commentModal').modal('hide');
            $(`#feedback-text-${id}`).text(`"${comments}"`);
            showToast('success', 'Feedback saved successfully!');
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.error || 'Something went wrong');
        }
    });
}
</script>
@endpush
@endsection
