export function loadRatings() {
    fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/get_ratings.php')
    .then(response => response.json())
    .then(data => {
        if (data.estado === "success") {
            updateRatingDisplay(data.ratings);
        } else {
            console.error("Error:", data.mensaje);
        }
    })
    .catch(error => console.error("Error:", error));
}

export function updateRatingDisplay(ratings) {
    const totalRatings = ratings.reduce((sum, rating) => sum + rating.count, 0);
    const averageRating = ratings.reduce((sum, rating) => sum + rating.stars * rating.count, 0) / totalRatings;

    document.getElementById('average-rating').textContent = averageRating.toFixed(1);
    document.getElementById('rating-count').textContent = `(${totalRatings})`;

    ratings.forEach(rating => {
        const bar = document.getElementById(`bar-${rating.stars}`);
        const count = document.getElementById(`count-${rating.stars}`);
        const percentage = (rating.count / totalRatings) * 100;
        bar.style.width = `${percentage}%`;
        count.textContent = rating.count;
    });
}
