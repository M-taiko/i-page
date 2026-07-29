{{-- QR scan / manual entry modal — self-contained, safe to include from any page.
     Requires: openQrModal() to be wired to a trigger button on the including page. --}}
<style>
    .qr-scan-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 50;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
    }

    .qr-scan-modal-overlay.show { display: flex; }

    .qr-scan-modal {
        background-color: var(--surface-bg);
        border-radius: var(--radius-xl);
        padding: var(--space-8) var(--space-6);
        max-width: 340px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    }

    .qr-scan-modal i.qr-scan-icon { font-size: 2.5rem; color: var(--primary-600); display: block; margin-bottom: var(--space-3); }
    .qr-scan-modal h3 { font-size: var(--text-lg); margin-bottom: var(--space-2); color: var(--text-primary); }
    .qr-scan-modal p { color: var(--text-secondary); margin-bottom: var(--space-5); font-size: var(--text-sm); }
    .qr-scan-modal-actions { display: flex; gap: var(--space-3); }

    .qr-scan-modal-actions button, .qr-scan-modal-actions a {
        flex: 1;
        padding: var(--space-3);
        border-radius: var(--radius-md);
        font-weight: var(--font-weight-medium);
        text-decoration: none;
        font-size: var(--text-sm);
        cursor: pointer;
        border: none;
    }

    .qr-scan-modal-actions .btn-cancel { background-color: var(--surface-hover); color: var(--text-primary); }
    .qr-scan-modal-actions .btn-go { background-color: var(--primary-600); color: white; display: flex; align-items: center; justify-content: center; }
</style>

<div class="qr-scan-modal-overlay" id="qrModal" onclick="if(event.target===this) closeQrModal()">
    <div class="qr-scan-modal" style="max-width: 340px;">
        <i class="bi bi-qr-code-scan qr-scan-icon"></i>
        <h3>{{ __('Join a Channel') }}</h3>
        <p>{{ __('Scan a QR code or type the code / link printed on it.') }}</p>

        <video id="qrVideo" playsinline muted style="display: none; width: 100%; border-radius: var(--radius-md); margin-bottom: var(--space-3);"></video>
        <button type="button" id="qrCameraBtn" class="btn-go" style="width: 100%; margin-bottom: var(--space-3); display: none;" onclick="startQrCamera()">
            <i class="bi bi-camera"></i>&nbsp; {{ __('Use Camera') }}
        </button>
        <p id="qrCameraStatus" style="display: none; font-size: var(--text-xs); color: var(--text-tertiary); margin-bottom: var(--space-3);"></p>

        @if(session('error'))
            <p style="color: var(--danger-600); font-size: var(--text-sm); margin-bottom: var(--space-3);">{{ session('error') }}</p>
        @endif

        <form action="{{ route('qr.lookup') }}" method="GET">
            <input type="text" name="code" placeholder="{{ __('Channel code or link') }}" required
                   style="width: 100%; padding: var(--space-3); border-radius: var(--radius-md); border: 1px solid var(--surface-border); margin-bottom: var(--space-3); font-size: var(--text-sm);">
            <div class="qr-scan-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeQrModal()">{{ __('Cancel') }}</button>
                <button type="submit" class="btn-go">{{ __('Go') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    let qrStream = null;
    let qrDetectTimer = null;

    function openQrModal() {
        document.getElementById('qrModal').classList.add('show');

        if ('BarcodeDetector' in window && navigator.mediaDevices?.getUserMedia) {
            startQrCamera();
        } else {
            const status = document.getElementById('qrCameraStatus');
            status.style.display = 'block';
            status.textContent = '{{ __('Your browser doesn\'t support in-app scanning — type the code below instead.') }}';
        }
    }

    function closeQrModal() {
        document.getElementById('qrModal').classList.remove('show');
        stopQrCamera();
        document.getElementById('qrVideo').style.display = 'none';
        document.getElementById('qrCameraBtn').style.display = 'none';
        document.getElementById('qrCameraStatus').style.display = 'none';
    }

    async function startQrCamera() {
        const video = document.getElementById('qrVideo');
        const status = document.getElementById('qrCameraStatus');
        const btn = document.getElementById('qrCameraBtn');

        try {
            qrStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = qrStream;
            video.style.display = 'block';
            btn.style.display = 'none';
            await video.play();

            const detector = new BarcodeDetector({ formats: ['qr_code'] });
            status.style.display = 'block';
            status.textContent = '{{ __('Point your camera at a QR code…') }}';

            qrDetectTimer = setInterval(async () => {
                try {
                    const codes = await detector.detect(video);
                    if (codes.length > 0) {
                        stopQrCamera();
                        window.location.href = '{{ route('qr.lookup') }}?code=' + encodeURIComponent(codes[0].rawValue);
                    }
                } catch (e) { /* keep scanning */ }
            }, 400);
        } catch (e) {
            video.style.display = 'none';
            status.style.display = 'block';
            status.textContent = '{{ __('Camera unavailable — type the code below, or try again.') }}';
            btn.textContent = '';
            btn.innerHTML = '<i class="bi bi-camera"></i>&nbsp; {{ __('Retry Camera') }}';
            btn.style.display = 'block';
        }
    }

    function stopQrCamera() {
        if (qrDetectTimer) { clearInterval(qrDetectTimer); qrDetectTimer = null; }
        if (qrStream) { qrStream.getTracks().forEach(t => t.stop()); qrStream = null; }
    }

    @if(session('error'))
        document.addEventListener('DOMContentLoaded', () => openQrModal());
    @endif
</script>
