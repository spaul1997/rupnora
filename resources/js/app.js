import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);

Alpine.store('ui', {
    mobileMenuOpen: false,
    searchOpen: false,
    filterDrawerOpen: false,
    cartCount: 3,
    wishlistIds: ['eternal-bloom-diamond-ring', 'royal-heritage-gold-necklace'],
    toasts: [],
    toastId: 0,

    toggleWishlist(id) {
        const idx = this.wishlistIds.indexOf(id);
        if (idx === -1) {
            this.wishlistIds.push(id);
            this.notify('Added to wishlist', 'success');
        } else {
            this.wishlistIds.splice(idx, 1);
            this.notify('Removed from wishlist', 'default');
        }
    },

    isWishlisted(id) {
        return this.wishlistIds.includes(id);
    },

    addToCart(name = 'Item') {
        this.cartCount++;
        this.notify(`${name} added to cart`, 'success');
    },

    notify(message, type = 'default') {
        const id = ++this.toastId;
        this.toasts.push({ id, message, type });
        setTimeout(() => {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        }, 3200);
    },
});

Alpine.start();
