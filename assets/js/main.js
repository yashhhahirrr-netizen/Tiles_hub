/* assets/js/main.js - TilePoint Dynamic Interactivity & API Call Handlers */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Toast Notification System
    window.showToast = function(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };

    // 2. Search Overlay Toggle
    const searchOpenBtns = document.querySelectorAll('.js-search-open');
    const searchCloseBtn = document.querySelector('.js-search-close');
    const searchOverlay = document.getElementById('search-overlay');

    if (searchOverlay) {
        searchOpenBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                searchOverlay.classList.add('active');
                document.getElementById('search-input')?.focus();
            });
        });

        searchCloseBtn?.addEventListener('click', () => {
            searchOverlay.classList.remove('active');
        });
    }

    // 3. Cart Operations (AJAX)
    document.querySelectorAll('.js-add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const boxes = this.dataset.boxes || 1;

            fetch(window.BASE_URL + '/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'add',
                    product_id: productId,
                    boxes: boxes,
                    csrf_token: window.CSRF_TOKEN || ''
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Tile added to cart!', 'success');
                    const badge = document.querySelector('.js-cart-count');
                    if (badge) badge.textContent = data.cart_count;
                } else {
                    showToast(data.message || 'Failed to add to cart', 'error');
                }
            })
            .catch(() => showToast('Error processing request', 'error'));
        });
    });

    // 4. Wishlist Toggle (AJAX)
    document.querySelectorAll('.js-wishlist-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;

            fetch(window.BASE_URL + '/api/wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'toggle',
                    product_id: productId,
                    csrf_token: window.CSRF_TOKEN || ''
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    this.classList.toggle('active');
                    const badge = document.querySelector('.js-wishlist-count');
                    if (badge) badge.textContent = data.wishlist_count;
                } else {
                    showToast(data.message || 'Please log in to use wishlist', 'warning');
                }
            })
            .catch(() => showToast('Error updating wishlist', 'error'));
        });
    });

    // 5. Quantity Box Counter Adjustment
    document.querySelectorAll('.js-qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            let val = parseInt(input.value) || 1;
            if (this.dataset.dir === 'plus') {
                val++;
            } else if (this.dataset.dir === 'minus' && val > 1) {
                val--;
            }
            input.value = val;
            input.dispatchEvent(new Event('change'));
        });
    });
});
