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
        @php
            $ratingRecord  = $staff->ratings->first();
            $currentRating = $ratingRecord ? (int)$ratingRecord->rating : 0;
        @endphp
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="card-body p-4">
                    {{-- Staff Info --}}
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div>
                            <img src="{{ $staff->user->avatar_url }}"
                                 class="rounded-circle border border-2 border-white shadow-sm"
                                 width="60" height="60" alt="{{ $staff->user->name }}">
                        </div>
                        <div>
                            <h6 class="fw-800 mb-0">{{ $staff->user->name }}</h6>
                            <span class="badge bg-soft-primary text-primary px-2 py-1 small rounded-pill">Field Staff</span>
                            <div class="text-muted small mt-1">
                                <i class="fa fa-phone me-1"></i> {{ $staff->user->contact_no ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    {{-- Rating Display --}}
                    <div class="rating-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold">Performance Rating</span>
                            <span class="rating-label small text-muted" id="rating-label-{{ $staff->id }}">
                                {{ $currentRating > 0 ? $currentRating . '/5' : 'Not Rated' }}
                            </span>
                        </div>

                        {{-- Read-only stars on the card (display only) --}}
                        <div class="stars-display mb-3" id="stars-display-{{ $staff->id }}">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="card-star"
                                      style="color: {{ $i <= $currentRating ? '#FFC107' : '#D0D0D0' }};
                                             font-size: 2rem;
                                             text-shadow: {{ $i <= $currentRating ? '0 0 4px rgba(255,193,7,0.5)' : 'none' }};">&#9733;</span>
                            @endfor
                        </div>

                        {{-- Feedback Text --}}
                        <p class="text-muted small fst-italic mb-3" id="feedback-text-{{ $staff->id }}">
                            {{ $ratingRecord && $ratingRecord->comments ? '"' . $ratingRecord->comments . '"' : 'No feedback provided yet.' }}
                        </p>

                        {{-- Action Button --}}
                        <button class="btn btn-primary btn-sm w-100 rounded-pill py-2 fw-bold"
                                onclick="openFeedbackModal({{ $staff->id }}, '{{ addslashes($staff->user->name) }}', {{ $currentRating }}, '{{ addslashes($ratingRecord?->comments ?? '') }}')">
                            <i class="fa fa-comment me-2"></i>
                            {{ $ratingRecord ? 'Update Feedback' : 'Add Feedback' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                <i class="fa fa-users fa-3x text-muted mb-3"></i>
                <h5>No Field Staff Assigned</h5>
                <p class="text-muted">Once field staff are assigned to your orders or retailers, they will appear here for rating.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- Feedback Modal --}}
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-800">Feedback for <span id="modal-staff-name"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modal-staff-id">
                <input type="hidden" id="modal-selected-rating" value="0">

                {{-- Star Picker --}}
                <div class="mb-4 text-center">
                    <label class="form-label small fw-bold d-block mb-3">Tap to Rate</label>
                    <div id="modal-star-picker" class="modal-star-row">
                        <span class="modal-star" data-val="1" style="color:#D0D0D0;font-size:3.2rem;cursor:pointer;transition:transform 0.1s,color 0.1s;user-select:none;">&#9733;</span>
                        <span class="modal-star" data-val="2" style="color:#D0D0D0;font-size:3.2rem;cursor:pointer;transition:transform 0.1s,color 0.1s;user-select:none;">&#9733;</span>
                        <span class="modal-star" data-val="3" style="color:#D0D0D0;font-size:3.2rem;cursor:pointer;transition:transform 0.1s,color 0.1s;user-select:none;">&#9733;</span>
                        <span class="modal-star" data-val="4" style="color:#D0D0D0;font-size:3.2rem;cursor:pointer;transition:transform 0.1s,color 0.1s;user-select:none;">&#9733;</span>
                        <span class="modal-star" data-val="5" style="color:#D0D0D0;font-size:3.2rem;cursor:pointer;transition:transform 0.1s,color 0.1s;user-select:none;">&#9733;</span>
                    </div>
                    <div class="mt-2 small text-muted" id="modal-rating-text">Not rated yet</div>
                </div>

                {{-- Comments --}}
                <div class="mb-2">
                    <label class="form-label small fw-bold">Comments <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea id="modal-comments" class="form-control rounded-3" rows="4"
                              placeholder="Write your feedback here..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="saveFeedbackBtn" onclick="saveFeedback()">
                    <i class="fa fa-save me-1"></i> Save Feedback
                </button>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    /* ---- Card display stars ---- */
    .stars-display {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    .card-star {
        font-size: 1.6rem;
        color: #d0d0d0 !important;
        line-height: 1;
        display: inline-block;
    }
    .card-star.filled {
        color: #FFC107 !important;
        text-shadow: 0 0 2px rgba(255,193,7,0.4);
    }

    /* ---- Modal star picker ---- */
    .modal-star-row {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .modal-star {
        font-size: 2.6rem;
        color: #d0d0d0 !important;
        cursor: pointer;
        transition: color 0.12s ease, transform 0.1s ease;
        line-height: 1;
        user-select: none;
    }
    .modal-star.filled,
    .modal-star.hovered {
        color: #FFC107 !important;
        transform: scale(1.18);
        text-shadow: 0 0 6px rgba(255,193,7,0.5);
    }

    /* ---- Misc ---- */
    .bg-soft-primary { background-color: rgba(var(--bs-primary-rgb), 0.1); }
    .fw-800 { font-weight: 800; }

    .rating-labels { font-size: 0.75rem; }
</style>
@endpush

@push('scripts')
<script>
/* =========================================================
   Feedback Modal Logic
   ========================================================= */
function openFeedbackModal(staffId, staffName, currentRating, existingComment) {
    $('#modal-staff-id').val(staffId);
    $('#modal-staff-name').text(staffName);
    $('#modal-comments').val(existingComment);

    // Pre-fill stars
    setModalRating(currentRating);

    $('#feedbackModal').modal('show');
}

function setModalRating(val) {
    $('#modal-selected-rating').val(val);
    $('#modal-star-picker .modal-star').each(function(i) {
        if (i < val) {
            $(this).css({'color': '#FFC107', 'transform': 'scale(1.1)', 'text-shadow': '0 0 6px rgba(255,193,7,0.5)'});
        } else {
            $(this).css({'color': '#D0D0D0', 'transform': 'scale(1)', 'text-shadow': 'none'});
        }
    });
    const labels = ['Not rated yet', 'Poor ⭐', 'Fair ⭐⭐', 'Good ⭐⭐⭐', 'Very Good ⭐⭐⭐⭐', 'Excellent ⭐⭐⭐⭐⭐'];
    $('#modal-rating-text').text(val > 0 ? labels[val] + ' (' + val + '/5)' : 'Not rated yet');
}

$(document).ready(function () {
    // Modal star hover
    $(document).on('mouseenter', '.modal-star', function () {
        const val = parseInt($(this).data('val'));
        const current = parseInt($('#modal-selected-rating').val()) || 0;
        $('#modal-star-picker .modal-star').each(function (i) {
            if (i < val) {
                $(this).css({'color': '#FFC107', 'transform': 'scale(1.18)', 'text-shadow': '0 0 8px rgba(255,193,7,0.6)'});
            } else {
                $(this).css({'color': '#D0D0D0', 'transform': 'scale(1)', 'text-shadow': 'none'});
            }
        });
    });
    $(document).on('mouseleave', '#modal-star-picker', function () {
        // Restore to current selected state
        setModalRating(parseInt($('#modal-selected-rating').val()) || 0);
    });

    // Modal star click
    $(document).on('click', '.modal-star', function () {
        const val = parseInt($(this).data('val'));
        setModalRating(val);
    });
});

function saveFeedback() {
    const staffId  = $('#modal-staff-id').val();
    const rating   = parseInt($('#modal-selected-rating').val()) || 0;
    const comments = $('#modal-comments').val().trim();

    if (rating === 0) {
        showToast('warning', 'Please select a star rating before saving.');
        $('#modal-star-picker .modal-star').first().addClass('shake');
        setTimeout(() => $('#modal-star-picker .modal-star').removeClass('shake'), 500);
        return;
    }

    const $btn = $('#saveFeedbackBtn');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

    $.ajax({
        url: "{{ route('distributor.staff-ratings.store') }}",
        method: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            field_staff_id: staffId,
            rating: rating,
            category: 'general',
            comments: comments
        },
        success: function (response) {
            $('#feedbackModal').modal('hide');

            // Update card display stars with inline colors
            const starsHtml = [1,2,3,4,5].map(i =>
                `<span class="card-star" style="color:${i <= rating ? '#FFC107' : '#D0D0D0'};font-size:2rem;text-shadow:${i <= rating ? '0 0 4px rgba(255,193,7,0.5)' : 'none'};">&#9733;</span>`
            ).join('');
            $(`#stars-display-${staffId}`).html(starsHtml);

            // Update label
            $(`#rating-label-${staffId}`).text(rating + '/5');

            // Update feedback text
            $(`#feedback-text-${staffId}`).text(comments ? `"${comments}"` : 'No feedback provided yet.');

            showToast('success', 'Feedback saved successfully!');
        },
        error: function (xhr) {
            let errMsg = 'Failed to save feedback.';
            if (xhr.responseJSON) {
                errMsg = xhr.responseJSON.message || xhr.responseJSON.error || errMsg;
            }
            showToast('error', errMsg);
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Feedback');
        }
    });
}
</script>
@endpush
@endsection
