import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);

const initialState = window.rupnoraInitialState || {};
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const jsonHeaders = () => ({
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken(),
});

Alpine.store('ui', {
    mobileMenuOpen: false,
    searchOpen: false,
    filterDrawerOpen: false,
    cartCount: Number(initialState.cartCount || 0),
    wishlistIds: (initialState.wishlistIds || []).map((id) => String(id)),
    toasts: [],
    toastId: 0,

    setCartCount(count) {
        this.cartCount = Math.max(0, Number(count) || 0);
    },

    setWishlistIds(ids) {
        this.wishlistIds = [...new Set((ids || []).map((id) => String(id)))];
    },

    addWishlist(id) {
        const key = String(id);

        if (!this.wishlistIds.includes(key)) {
            this.wishlistIds.push(key);
        }
    },

    async toggleWishlist(item) {
        const payload = typeof item === 'object' ? item : { id: item };
        const key = String(payload.id);
        const idx = this.wishlistIds.indexOf(key);
        const adding = idx === -1;
        const previousWishlistIds = [...this.wishlistIds];

        if (adding) {
            this.wishlistIds.push(key);
        } else {
            this.wishlistIds.splice(idx, 1);
        }

        if (payload.addUrl && payload.removeUrl) {
            try {
                const response = await fetch(adding ? payload.addUrl : payload.removeUrl, {
                    method: adding ? 'POST' : 'DELETE',
                    headers: jsonHeaders(),
                    body: adding ? JSON.stringify({ product_id: key }) : null,
                });

                if (!response.ok) {
                    throw new Error('Wishlist request failed');
                }

                const data = await response.json();
                this.setWishlistIds(data.wishlistIds || []);
            } catch (error) {
                this.setWishlistIds(previousWishlistIds);
                this.notify('Unable to update wishlist. Please try again.', 'error');
                return null;
            }
        }

        if (adding) {
            this.notify('Added to wishlist', 'success');
        } else {
            this.notify('Removed from wishlist', 'default');
        }

        return true;
    },

    isWishlisted(id) {
        return this.wishlistIds.includes(String(id));
    },

    async addToCart(item = 'Item') {
        const payload = typeof item === 'object' ? item : { name: item };
        const name = payload.name || 'Item';

        if (payload.id) {
            try {
                const response = await fetch(payload.url || '/cart', {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({
                        product_id: payload.id,
                        qty: payload.qty || 1,
                        size: payload.size || null,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Cart request failed');
                }

                const data = await response.json();
                this.setCartCount(data.count);
            } catch (error) {
                this.notify('Unable to update cart. Please try again.', 'error');
                return null;
            }
        } else {
            this.setCartCount(this.cartCount + 1);
        }

        this.notify(`${name} added to cart`, 'success');
        return true;
    },

    notify(message, type = 'default') {
        const id = ++this.toastId;
        this.toasts.push({ id, message, type });
        setTimeout(() => {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        }, 3200);
    },
});

window.cartPage = (initialItems = []) => ({
    items: initialItems.map((item) => ({
        ...item,
        price: Number(item.price) || 0,
        mrp: Number(item.mrp) || 0,
        qty: Number(item.qty) || 1,
        max: Number(item.max) || 5,
        removed: false,
        syncing: false,
        lastSyncedQty: Number(item.qty) || 1,
    })),

    get hasItems() {
        return this.items.some((item) => !item.removed);
    },

    get mrpTotal() {
        return this.items.reduce((total, item) => item.removed ? total : total + (item.mrp * item.qty), 0);
    },

    get sellingTotal() {
        return this.items.reduce((total, item) => item.removed ? total : total + (item.price * item.qty), 0);
    },

    get discount() {
        return Math.max(0, this.mrpTotal - this.sellingTotal);
    },

    get tax() {
        return Math.round(this.sellingTotal * 0.03);
    },

    get total() {
        return this.sellingTotal + this.tax;
    },

    init() {
        this.syncCartCount();
    },

    isItemVisible(index) {
        return Boolean(this.items[index] && !this.items[index].removed);
    },

    itemLinePrice(index) {
        const item = this.items[index];

        return item && !item.removed ? item.price * item.qty : 0;
    },

    itemLineMrp(index) {
        const item = this.items[index];

        return item && !item.removed ? item.mrp * item.qty : 0;
    },

    formatMoney(value) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0,
        }).format(Math.round(Number(value) || 0));
    },

    clampQty(item) {
        item.qty = Math.max(1, Math.min(Number(item.max) || 5, Number(item.qty) || 1));
    },

    syncCartCount() {
        Alpine.store('ui').setCartCount(this.items.filter((item) => !item.removed).length);
    },

    async updateItem(index) {
        const item = this.items[index];

        if (!item || item.removed || item.syncing) {
            return;
        }

        const previousQty = item.lastSyncedQty;
        this.clampQty(item);
        const response = await this.request(item.updateUrl, 'PATCH', { qty: item.qty }, index);

        if (response) {
            item.lastSyncedQty = item.qty;
        } else {
            item.qty = previousQty;
        }
    },

    async removeItem(index) {
        const item = this.items[index];

        if (!item || item.removed || item.syncing) {
            return;
        }

        item.removed = true;
        this.syncCartCount();

        const response = await this.request(item.removeUrl, 'DELETE', null, index);

        if (response) {
            Alpine.store('ui').notify(`${item.name} removed from cart`);
        } else {
            item.removed = false;
            this.syncCartCount();
        }
    },

    async moveToWishlist(index) {
        const item = this.items[index];

        if (!item || item.removed || item.syncing) {
            return;
        }

        item.removed = true;
        const ui = Alpine.store('ui');
        const previousWishlistIds = [...ui.wishlistIds];
        ui.addWishlist(item.wishlistId || item.id);
        this.syncCartCount();

        const response = await this.request(item.wishlistUrl, 'POST', null, index);

        if (response) {
            if (response.wishlistIds) {
                Alpine.store('ui').setWishlistIds(response.wishlistIds);
            }

            Alpine.store('ui').notify(`${item.name} moved to wishlist`, 'success');
        } else {
            ui.setWishlistIds(previousWishlistIds);
            item.removed = false;
            this.syncCartCount();
        }
    },

    async request(url, method, payload = null, index = null) {
        const item = index === null ? null : this.items[index];

        if (item) {
            item.syncing = true;
        }

        try {
            const response = await fetch(url, {
                method,
                headers: jsonHeaders(),
                body: payload ? JSON.stringify(payload) : null,
            });

            if (!response.ok) {
                throw new Error('Cart request failed');
            }

            const data = await response.json();

            if (typeof data.count !== 'undefined') {
                Alpine.store('ui').setCartCount(data.count);
            }

            return data;
        } catch (error) {
            Alpine.store('ui').notify('Unable to update cart. Please try again.', 'error');
            return null;
        } finally {
            if (item) {
                item.syncing = false;
            }
        }
    },
});

window.wishlistPage = (initialProducts = []) => ({
    products: initialProducts.map((product) => ({
        ...product,
        removed: false,
        syncing: false,
    })),

    get count() {
        return this.products.filter((product) => !product.removed).length;
    },

    get hasProducts() {
        return this.count > 0;
    },

    async removeProduct(index) {
        const product = this.products[index];

        if (!product || product.removed || product.syncing) {
            return;
        }

        const ui = Alpine.store('ui');
        const previousWishlistIds = [...ui.wishlistIds];

        product.syncing = true;
        product.removed = true;
        ui.setWishlistIds(previousWishlistIds.filter((id) => id !== String(product.id)));

        try {
            const response = await fetch(product.removeUrl, {
                method: 'DELETE',
                headers: jsonHeaders(),
            });

            if (!response.ok) {
                throw new Error('Wishlist request failed');
            }

            const data = await response.json();
            ui.setWishlistIds(data.wishlistIds || []);
            ui.notify(`${product.name} removed from wishlist`);
        } catch (error) {
            product.removed = false;
            ui.setWishlistIds(previousWishlistIds);
            ui.notify('Unable to update wishlist. Please try again.', 'error');
        } finally {
            product.syncing = false;
        }
    },
});

const blankCheckoutAddress = () => ({
    type: 'Home',
    name: '',
    phone: '',
    line1: '',
    line2: '',
    landmark: '',
    city: '',
    state: '',
    pincode: '',
    country: 'India',
});

window.checkoutPage = ({ addresses = [], selectedAddressId = null, addAddressUrl = null, placeOrderUrl = null } = {}) => ({
    step: 1,
    selectedAddress: selectedAddressId ?? addresses[0]?.id ?? null,
    addresses: addresses.map((address) => ({ ...address })),
    addAddressUrl,
    placeOrderUrl,
    delivery: 'standard',
    payment: 'upi',
    addingAddress: false,
    savingAddress: false,
    placingOrder: false,
    addressError: '',
    orderError: '',
    newAddress: blankCheckoutAddress(),

    get selectedAddressRecord() {
        return this.addresses.find((address) => address.id === this.selectedAddress) || null;
    },

    get canSaveAddress() {
        return ['name', 'phone', 'line1', 'city', 'state', 'pincode'].every((field) => {
            return String(this.newAddress[field] || '').trim().length > 0;
        });
    },

    selectAddress(id) {
        this.selectedAddress = id;
        this.addressError = '';
    },

    resetNewAddress() {
        this.newAddress = blankCheckoutAddress();
        this.addressError = '';
    },

    async saveAddress() {
        if (!this.canSaveAddress) {
            this.addressError = 'Please complete all required address fields.';
            return;
        }

        if (!/^[1-9][0-9]{5}$/.test(String(this.newAddress.pincode).trim())) {
            this.addressError = 'Please enter a valid 6-digit PIN code.';
            return;
        }

        const payload = {
            type: this.newAddress.type || 'Home',
            name: this.newAddress.name.trim(),
            phone: this.newAddress.phone.trim(),
            line1: this.newAddress.line1.trim(),
            line2: this.newAddress.line2.trim(),
            landmark: this.newAddress.landmark.trim(),
            city: this.newAddress.city.trim(),
            state: this.newAddress.state.trim(),
            pincode: this.newAddress.pincode.trim(),
            country: this.newAddress.country.trim() || 'India',
        };

        this.savingAddress = true;
        this.addressError = '';

        if (this.addAddressUrl) {
            try {
                const response = await fetch(this.addAddressUrl, {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    throw new Error('Address request failed');
                }

                const data = await response.json();
                this.addresses = data.addresses || [...this.addresses, data.address];
                this.selectAddress(data.address.id);
                this.resetNewAddress();
                this.addingAddress = false;
                Alpine.store('ui').notify(data.message || 'Address added', 'success');
                return;
            } catch (error) {
                this.addressError = 'Unable to save address. Please try again.';
                return;
            } finally {
                this.savingAddress = false;
            }
        }

        const address = {
            id: `new-${Date.now()}`,
            default: this.addresses.length === 0,
            ...payload,
        };

        this.addresses.push(address);
        this.selectAddress(address.id);
        this.resetNewAddress();
        this.addressError = '';
        this.addingAddress = false;
        this.savingAddress = false;
        Alpine.store('ui').notify('Address added', 'success');
    },

    continueToDelivery() {
        if (!this.selectedAddressRecord) {
            this.addressError = 'Please select or add a delivery address.';
            return;
        }

        this.addressError = '';
        this.step = 2;
    },

    async placeOrder() {
        if (!this.selectedAddressRecord) {
            this.orderError = 'Please select or add a delivery address.';
            this.step = 1;
            return;
        }

        if (!this.placeOrderUrl) {
            this.orderError = 'Unable to place order. Please refresh and try again.';
            return;
        }

        this.placingOrder = true;
        this.orderError = '';

        try {
            const response = await fetch(this.placeOrderUrl, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    address_id: this.selectedAddress,
                    delivery: this.delivery,
                    payment: this.payment,
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const firstError = Object.values(data.errors || {})[0]?.[0];
                throw new Error(firstError || data.message || 'Unable to place order.');
            }

            if (data.cartCount !== undefined) {
                Alpine.store('ui').setCartCount(data.cartCount);
            }

            Alpine.store('ui').notify(data.message || 'Order placed successfully', 'success');
            window.location.href = data.redirect;
        } catch (error) {
            this.orderError = error.message || 'Unable to place order. Please try again.';
            this.placingOrder = false;
        }
    },

    compactAddress(address) {
        if (!address) {
            return '';
        }

        return [address.name, address.line1, address.city, address.pincode]
            .filter(Boolean)
            .join(', ');
    },

    fullAddress(address) {
        if (!address) {
            return '';
        }

        const area = [address.line1, address.line2, address.landmark].filter(Boolean).join(', ');
        const cityLine = [address.city, address.state, address.pincode].filter(Boolean).join(' ');

        return [area, cityLine, address.country].filter(Boolean).join(' - ');
    },
});

Alpine.start();
