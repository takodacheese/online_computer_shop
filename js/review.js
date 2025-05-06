document.addEventListener('DOMContentLoaded', function() {
    const ratingText = document.getElementById('rating-text');
    const starLabels = document.querySelectorAll('.rating-selector label');

    starLabels.forEach(label => {
        label.addEventListener('mouseover', function() {
            const rating = this.previousElementSibling.value;
            updateRatingText(rating);
        });

        label.addEventListener('mouseout', function() {
            const checkedRating = document.querySelector('.rating-selector input[type="radio"]:checked');
            if (checkedRating) {
                updateRatingText(checkedRating.value);
            } else {
                ratingText.textContent = 'Select a rating';
            }
        });

        label.addEventListener('click', function() {
            const rating = this.previousElementSibling.value;
            updateRatingText(rating);
        });
    });

    function updateRatingText(rating) {
        const ratings = {
            '1': 'Very Poor',
            '2': 'Poor',
            '3': 'Average',
            '4': 'Good',
            '5': 'Excellent'
        };
        ratingText.textContent = ratings[rating] || 'Select a rating';
    }
});
