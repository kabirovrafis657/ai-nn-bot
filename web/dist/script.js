let selectedMode = '';
let selectedFile = null;
let userCredits = 0;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeUpload();
    checkUserCredits();
});

function confirmAge(isAdult) {
    if (isAdult) {
        document.getElementById('ageVerification').style.display = 'none';
        document.getElementById('mainContent').style.display = 'block';
        document.getElementById('mainContent').classList.add('fade-in');
        
        // Show agreement message
        const lang = getCurrentLanguage();
        showNotification(translations[lang].agreement, 'info');
    } else {
        const lang = getCurrentLanguage();
        showNotification(translations[lang].age_restriction, 'error');
    }
}

function selectMode(mode) {
    selectedMode = mode;
    
    // Update UI to show selected mode
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    event.target.classList.add('selected');
    
    // Show upload section
    document.getElementById('uploadSection').style.display = 'block';
    document.getElementById('uploadSection').scrollIntoView({ behavior: 'smooth' });
    
    const lang = getCurrentLanguage();
    const modeNames = {
        'undress': translations[lang].remove_clothes,
        'bikini': translations[lang].add_swimsuit,
        'lingerie': translations[lang].add_bra,
        'bath_towel': translations[lang].add_towel
    };
    
    showNotification(`${translations[lang].mode_selected} ${modeNames[mode]}`, 'success');
}

function initializeUpload() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    
    // Click to upload
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });
    
    // File input change
    fileInput.addEventListener('change', handleFileSelect);
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });
}

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        handleFile(file);
    }
}

function handleFile(file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
        const lang = getCurrentLanguage();
        showNotification(translations[lang].incorrect_format, 'error');
        return;
    }
    
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        showNotification('File size too large. Please select an image under 10MB.', 'error');
        return;
    }
    
    selectedFile = file;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const uploadArea = document.getElementById('uploadArea');
        uploadArea.innerHTML = `<img src="${e.target.result}" alt="Selected image">`;
        document.getElementById('processBtn').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function processImage() {
    if (!selectedFile || !selectedMode) {
        showNotification('Please select a mode and upload an image.', 'error');
        return;
    }
    
    // Check if user has credits
    if (userCredits <= 0) {
        showPaymentSection();
        return;
    }
    
    // Show processing
    document.getElementById('uploadSection').style.display = 'none';
    document.getElementById('processing').style.display = 'block';
    
    // Convert file to base64
    const reader = new FileReader();
    reader.onload = function(e) {
        const base64Image = e.target.result;
        
        // Call API with a longer timeout for processing
        processWithAPI(base64Image);
    };
    reader.readAsDataURL(selectedFile);
}

async function processWithAPI(base64Image) {
    try {
        // Show processing message
        const lang = getCurrentLanguage();
        showNotification(translations[lang].processing, 'info');
        
        // For demo purposes, we'll simulate the processing
        // In a real implementation, this would call your actual API
        const response = await simulateAPICall(base64Image);
        
        if (response.success) {
            showResult(base64Image, response.processedImage);
            userCredits--;
            updateCreditsDisplay();
            
            const lang = getCurrentLanguage();
            showNotification('Processing completed successfully!', 'success');
        } else {
            throw new Error('Processing failed');
        }
    } catch (error) {
        console.error('Error:', error);
        const lang = getCurrentLanguage();
        showNotification(translations[lang].photo_error, 'error');
        
        // Reset to upload section
        document.getElementById('processing').style.display = 'none';
        document.getElementById('uploadSection').style.display = 'block';
    }
}

async function simulateAPICall(base64Image) {
    // Simulate API processing time (2-5 seconds)
    const processingTime = Math.random() * 3000 + 2000;
    
    return new Promise((resolve) => {
        setTimeout(() => {
            // For demo purposes, return the same image as "processed"
            // In a real implementation, this would be the actual processed image
            resolve({
                success: true,
                processedImage: base64Image
            });
        }, processingTime);
    });
}

function showResult(originalImage, processedImage) {
    document.getElementById('processing').style.display = 'none';
    document.getElementById('resultSection').style.display = 'block';
    
    document.getElementById('originalImage').src = originalImage;
    document.getElementById('processedImage').src = processedImage;
    
    document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth' });
}

function downloadResult() {
    const processedImage = document.getElementById('processedImage');
    const link = document.createElement('a');
    link.download = 'processed-image.png';
    link.href = processedImage.src;
    link.click();
}

function processAnother() {
    // Reset the form
    selectedFile = null;
    selectedMode = '';
    
    // Reset UI
    document.getElementById('resultSection').style.display = 'none';
    document.getElementById('paymentSection').style.display = 'none';
    document.getElementById('uploadSection').style.display = 'none';
    
    // Reset upload area
    const uploadArea = document.getElementById('uploadArea');
    const lang = getCurrentLanguage();
    uploadArea.innerHTML = `
        <div class="upload-placeholder">
            <i class="upload-icon">📷</i>
            <p data-translate="click_to_upload">${translations[lang].click_to_upload}</p>
        </div>
    `;
    
    // Reset mode selection
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    document.getElementById('processBtn').style.display = 'none';
    document.getElementById('fileInput').value = '';
    
    // Scroll to mode selection
    document.getElementById('modeSelection').scrollIntoView({ behavior: 'smooth' });
}

function showPaymentSection() {
    document.getElementById('uploadSection').style.display = 'none';
    document.getElementById('paymentSection').style.display = 'block';
    document.getElementById('paymentSection').scrollIntoView({ behavior: 'smooth' });
}

function purchaseCredits() {
    // Simulate Telegram Stars payment
    // In a real implementation, this would integrate with Telegram WebApp API
    
    const lang = getCurrentLanguage();
    
    // Simulate payment processing
    showNotification('Processing payment...', 'info');
    
    setTimeout(() => {
        showNotification(translations[lang].payment_success, 'success');
        
        userCredits++;
        updateCreditsDisplay();
        
        // Hide payment section and show upload section
        document.getElementById('paymentSection').style.display = 'none';
        document.getElementById('uploadSection').style.display = 'block';
        document.getElementById('uploadSection').scrollIntoView({ behavior: 'smooth' });
    }, 2000);
}

function checkUserCredits() {
    // In a real implementation, this would check the user's credits from the server
    // For demo purposes, we'll start with 1 free credit
    userCredits = parseInt(localStorage.getItem('userCredits') || '1');
    updateCreditsDisplay();
}

function updateCreditsDisplay() {
    localStorage.setItem('userCredits', userCredits.toString());
    
    // Update credits display in UI
    const creditsDisplay = document.getElementById('creditsDisplay');
    if (creditsDisplay) {
        creditsDisplay.textContent = userCredits;
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'error' ? '#ff4757' : type === 'success' ? '#2ed573' : '#5352ed'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 1000;
        max-width: 300px;
        animation: slideIn 0.3s ease-out;
        font-weight: 500;
    `;
    notification.textContent = message;
    
    // Add animation styles if not already added
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Remove notification after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideIn 0.3s ease-out reverse';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Add credits display to header
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    const creditsDiv = document.createElement('div');
    creditsDiv.innerHTML = `
        <div style="color: white; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <span>Credits:</span>
            <span id="creditsDisplay" style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 15px;">${userCredits}</span>
        </div>
    `;
    header.appendChild(creditsDiv);
});