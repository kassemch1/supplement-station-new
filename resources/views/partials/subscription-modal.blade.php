{{-- Newsletter popup; resources/assets/js/store.js shows it after $delay ms when the visitor has not answered yet --}}
<div id="subscriptionModal" class="subscription-modal" style="display: none;" data-delay="{{ $delay ?? 3000 }}">
    <div class="subscription-modal-overlay">
        <div class="subscription-modal-content">
            <div class="subscription-modal-header">
                <span class="subscription-modal-icon"><i class="fas fa-dumbbell"></i></span>
                <h3>Stay Strong, Stay Updated!</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="subscription-modal-body">
                <p>Stay updated on the latest deals, exclusive offers, and new product alerts delivered to your house!</p>
                <form id="subscriptionForm">
                    <div class="form-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="subscriptionEmail" name="email" placeholder="Enter your email address" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-subscribe">Subscribe</button>
                        <button type="button" class="btn-cancel" id="cancelSubscription">No, Thanks</button>
                    </div>
                </form>
                <div id="subscriptionMessage" style="display: none; margin-top: 10px;"></div>
            </div>
        </div>
    </div>
</div>
