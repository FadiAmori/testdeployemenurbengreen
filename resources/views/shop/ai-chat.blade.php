<section class="ai-chat-widget" x-data="shopAiChat()" x-cloak>
    <h2 class="sr-only">Assistant de commande</h2>
    <div class="chat-panel" aria-live="polite" aria-busy="false">
        <ul class="chat-messages" x-ref="messages" role="log">
            <template x-for="message in messages" :key="message.id">
                <li :class="['chat-message', 'chat-message--' + message.role]">
                    <span class="chat-message__role" x-text="message.role === 'user' ? 'Vous' : 'Assistant'"></span>
                    <p class="chat-message__content" x-text="message.content"></p>
                </li>
            </template>
        </ul>
        <p class="chat-empty" x-show="messages.length === 0">Discutons de votre prochaine commande !</p>
        <p class="chat-error" x-show="error" x-text="error" role="alert"></p>
    </div>

    <form @submit.prevent="sendMessage" class="chat-form" aria-label="Envoyer un message à l’assistant">
        <label class="sr-only" for="ai-chat-input">Message</label>
        <textarea id="ai-chat-input" x-model="draft" :disabled="loading" rows="2" required maxlength="500"
            placeholder="Ex: Je veux 2 t-shirts verts M et une casquette noire, livraison Tunis"
            aria-required="true"></textarea>
        <button type="submit" class="alazea-btn" :disabled="loading" aria-label="Envoyer">
            <span x-show="!loading">Envoyer</span>
            <span x-show="loading" class="spinner"></span>
        </button>
    </form>

    <div class="chat-cart" x-show="cart" x-transition>
        <header class="chat-cart__header">
            <h3>Panier proposé</h3>
            <p class="chat-cart__confidence" x-show="cart?.confidence">
                Confiance&nbsp;: <strong x-text="(cart.confidence * 100).toFixed(0) + '%'" aria-live="polite"></strong>
            </p>
        </header>

        <p class="chat-cart__clarification" x-show="cart?.clarification" x-text="cart.clarification"></p>

        <ul class="chat-cart__items" x-show="cart?.items?.length">
            <template x-for="item in cart.items" :key="item.product_id">
                <li class="chat-cart__item">
                    <img :src="item.image_url || '{{ asset('urbangreen/img/bg-img/9.jpg') }}'" alt="" width="48" height="48"
                        class="chat-cart__item-image">
                    <div>
                        <p class="chat-cart__item-name" x-text="item.name"></p>
                        <p class="chat-cart__item-variant" x-text="formatVariant(item.variant)"></p>
                    </div>
                    <div class="chat-cart__item-meta">
                        <span x-text="item.quantity + ' × ' + formatPrice(item.unit_price)"></span>
                        <strong x-text="formatPrice(item.line_total)"></strong>
                    </div>
                </li>
            </template>
        </ul>

        <dl class="chat-cart__totals" x-show="cart?.totals">
            <div>
                <dt>Sous-total</dt>
                <dd x-text="formatPrice(cart.totals.subtotal)"></dd>
            </div>
            <div>
                <dt>Taxe</dt>
                <dd x-text="formatPrice(cart.totals.tax)"></dd>
            </div>
            <div>
                <dt>Remise</dt>
                <dd x-text="formatPrice(cart.totals.discount)"></dd>
            </div>
            <div class="chat-cart__grand-total">
                <dt>Total</dt>
                <dd x-text="formatPrice(cart.totals.total)"></dd>
            </div>
        </dl>

        <footer class="chat-cart__footer">
            <button type="button" class="btn btn-outline-secondary" @click="editCart">Modifier</button>
            <button type="button" class="btn btn-success" @click="confirmCart" :disabled="!cartToken || loading">
                Confirmer la commande
            </button>
        </footer>
    </div>

    <template x-if="cart?.shipping_address">
        <p class="chat-cart__shipping">
            <strong>Livraison :</strong>
            <span x-text="formatShipping(cart.shipping_address)"></span>
        </p>
    </template>
</section>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('shopAiChat', () => ({
            draft: '',
            messages: [],
            cart: null,
            cartToken: null,
            sessionUuid: null,
            loading: false,
            error: null,
            init() {
                this.fetchHistory();
            },
            async fetchHistory() {
                try {
                    const res = await fetch('{{ url('/shop/ai-chat/history') }}', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.messages = data.messages || [];
                    this.cart = data.cart || null;
                    if (data.session_uuid) {
                        this.sessionUuid = data.session_uuid;
                    }
                } catch (error) {
                    console.error(error);
                }
            },
            async sendMessage() {
                if (!this.draft.trim()) {
                    this.error = 'Merci de renseigner un message.';
                    return;
                }
                this.loading = true;
                this.error = null;
                try {
                    const res = await fetch('{{ url('/shop/ai-chat/message') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ message: this.draft }),
                        credentials: 'same-origin',
                    });

                    if (res.status === 429) {
                        this.error = 'Trop de requêtes. Merci de patienter quelques instants.';
                        return;
                    }

                    if (!res.ok) {
                        this.error = 'Impossible de contacter l’assistant pour le moment.';
                        return;
                    }

                    const data = await res.json();
                    this.messages.push({ id: Date.now(), role: 'user', content: this.draft });
                    this.messages.push({ id: Date.now() + 1, role: 'assistant', content: data.assistant_message });
                    this.cart = data.cart;
                    this.cartToken = data.cart_token;
                    if (data.session_uuid) {
                        this.sessionUuid = data.session_uuid;
                    }
                    this.draft = '';
                    this.$nextTick(() => {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    });
                } catch (error) {
                    console.error(error);
                    this.error = 'Une erreur est survenue.';
                } finally {
                    this.loading = false;
                }
            },
            async confirmCart() {
                if (!this.cartToken) {
                    this.error = 'Aucun panier à confirmer.';
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('{{ url('/shop/ai-chat/confirm') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ cart_token: this.cartToken, session_uuid: this.sessionUuid }),
                        credentials: 'same-origin',
                    });

                    if (res.status === 401) {
                        window.location.href = '{{ route('login') }}';
                        return;
                    }

                    if (!res.ok) {
                        const data = await res.json().catch(() => ({ message: 'Erreur lors de la confirmation.' }));
                        this.error = data.message || 'Erreur lors de la confirmation.';
                        return;
                    }

                    const data = await res.json();
                    if (data.redirect_url) {
                        // Rediriger immédiatement vers la page de checkout
                        window.location.assign(data.redirect_url);
                        return;
                    }
                    this.messages.push({ id: Date.now() + 2, role: 'assistant', content: 'Votre commande est confirmée !' });
                    this.cartToken = null;
                    this.cart = null;
                } catch (error) {
                    console.error(error);
                    this.error = 'Impossible de confirmer la commande pour le moment.';
                } finally {
                    this.loading = false;
                }
            },
            editCart() {
                this.error = 'Précisez ce que vous souhaitez modifier dans votre message.';
                this.cartToken = null;
            },
            formatPrice(value) {
                const number = Number(value || 0);
                return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(number);
            },
            formatVariant(variant) {
                if (!variant || Object.keys(variant).length === 0) {
                    return '';
                }
                return Object.entries(variant).map(([key, value]) => `${key}: ${value}`).join(', ');
            },
            formatShipping(shipping) {
                const parts = [];
                if (shipping.city) parts.push(`Ville: ${shipping.city}`);
                if (shipping.details) parts.push(shipping.details);
                return parts.join(' | ');
            },
        }));
    });
</script>
@endpush

@push('styles')
<style>
    .ai-chat-widget {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        background-color: #ffffff;
        margin-bottom: 2rem;
    }
    .chat-panel {
        max-height: 320px;
        overflow-y: auto;
        margin-bottom: 1rem;
    }
    .chat-messages {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: .75rem;
    }
    .chat-message {
        background: #f8fafc;
        border-radius: 8px;
        padding: .75rem;
    }
    .chat-message--user {
        background: #e3fcec;
    }
    .chat-message__role {
        font-weight: 600;
        display: block;
        margin-bottom: .25rem;
    }
    .chat-form {
        display: grid;
        gap: .5rem;
        margin-bottom: 1rem;
    }
    .chat-form textarea {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: .5rem .75rem;
        resize: vertical;
    }
    .chat-cart {
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
        margin-top: 1rem;
    }
    .chat-cart__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .chat-cart__items {
        list-style: none;
        padding: 0;
        margin: 0 0 1rem 0;
        display: grid;
        gap: .75rem;
    }
    .chat-cart__item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: .75rem;
        align-items: center;
    }
    .chat-cart__item-image {
        border-radius: 6px;
        object-fit: cover;
    }
    .chat-cart__totals {
        margin-bottom: 1rem;
    }
    .chat-cart__totals div {
        display: flex;
        justify-content: space-between;
        margin-bottom: .25rem;
    }
    .chat-cart__grand-total {
        font-weight: 700;
    }
    .chat-cart__footer {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
    }
    .chat-error {
        color: #c53030;
        margin-top: .5rem;
    }
    .spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid #fff;
        border-bottom-color: transparent;
        border-radius: 50%;
        display: inline-block;
        animation: spin 0.6s linear infinite;
    }
    .sr-only {
        position: absolute;
        clip: rect(1px, 1px, 1px, 1px);
        padding: 0;
        border: 0;
        height: 1px;
        width: 1px;
        overflow: hidden;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    @media (max-width: 768px) {
        .chat-cart__footer {
            flex-direction: column;
        }
    }
</style>
@endpush
