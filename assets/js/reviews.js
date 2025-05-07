class ReviewManager {
    constructor(containerId, type, typeId) {
        this.containerId = containerId;
        this.type = type;
        this.typeId = typeId;
        this.container = document.getElementById(containerId);
        this.currentPage = 1;
        this.init();
    }

    async init() {
        try {
            await this.loadReviews();
            this.setupReviewForm();
        } catch (error) {
            console.error('Initialization error:', error);
        }
    }

    async loadReviews() {
        try {
            const response = await fetch(`ajax/reviews.php?type=${this.type}&type_id=${this.typeId}&page=${this.currentPage}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.success) {
                this.renderReviews(data.reviews);
                this.renderPagination(data.pagination);
            } else {
                throw new Error(data.message || 'Failed to load reviews');
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
            this.showError('Failed to load reviews. Please try again later.');
        }
    }

    submitReview(formData) {
        fetch('ajax/reviews.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thank you!',
                    text: 'Your review has been submitted successfully.',
                    showConfirmButton: false,
                    timer: 1500
                });
                this.loadReviews(); // Reload reviews
                $('#reviewModal').modal('hide');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: data.message || 'Failed to submit review'
                });
            }
        })
        .catch(error => {
            console.error('Error submitting review:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to submit review. Please try again later.'
            });
        });
    }

    showError(message) {
        this.container.innerHTML = `
            <div class="alert alert-danger">
                ${message}
            </div>`;
    }

    renderReviews(reviews) {
        if (!reviews || reviews.length === 0) {
            this.container.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No reviews yet.</p>
                </div>`;
            return;
        }

        const reviewsHtml = reviews.map(review => this.renderReviewItem(review)).join('');
        this.container.innerHTML = reviewsHtml;
    }

    renderReviewItem(review) {
        const stars = this.renderStars(review.rating);
        const date = new Date(review.created_at).toLocaleDateString();
        
        return `
            <div class="review-item border-bottom pb-3 mb-3">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary-subtle text-primary rounded">
                                ${review.customer_name.charAt(0).toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mt-0 mb-1">${review.customer_name}</h5>
                        <div class="text-warning mb-2">
                            ${stars}
                            <span class="text-muted ms-2">${date}</span>
                        </div>
                        <p class="text-muted">${review.comment}</p>
                    </div>
                </div>
            </div>`;
    }

    renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="mdi mdi-star${i <= rating ? '' : '-outline'}"></i>`;
        }
        return stars;
    }
}