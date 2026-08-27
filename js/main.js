/* ==========================================================================
   BAZAARLY - CENTRAL MAIN JAVASCRIPT FILE (UPDATED)
   ========================================================================== */

let currentActiveSrc = '';

document.addEventListener("DOMContentLoaded", function () {

    // -------------------------------------------------------------
    // 1. HIDE / SHOW HEADER ON SCROLL
    // -------------------------------------------------------------
    let lastScrollTop = 0;
    const header = document.getElementById('mainHeader') || document.querySelector('header') || document.querySelector('.navbar');
    const delta = 5;

    if (header) {
        window.addEventListener('scroll', function() {
            let st = window.pageYOffset || document.documentElement.scrollTop;

            if (st <= 10) {
                header.classList.remove('nav-up');
                return;
            }

            if (Math.abs(lastScrollTop - st) <= delta) return;

            if (st > lastScrollTop && st > header.offsetHeight) {
                header.classList.add('nav-up');
            } else {
                if (st + window.innerHeight < document.documentElement.scrollHeight) {
                    header.classList.remove('nav-up');
                }
            }

            lastScrollTop = st;
        });
    }

    // -------------------------------------------------------------
    // 2. IMAGE SEARCH & DYNAMIC FORM SWITCHING LOGIC
    // -------------------------------------------------------------
    const cameraBtn = document.getElementById('cameraBtn');
    const imageSearchInput = document.getElementById('imageSearchInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewThumb = document.getElementById('imagePreviewThumb');
    const removeImgBtn = document.getElementById('removeImgBtn');
    const searchForm = document.getElementById('searchForm');

    if (cameraBtn && imageSearchInput) {
        cameraBtn.addEventListener('click', (e) => {
            e.preventDefault();
            imageSearchInput.click();
        });

        imageSearchInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewThumb && previewContainer) {
                        previewThumb.src = e.target.result;
                        previewContainer.style.display = 'inline-flex';
                    }
                };
                reader.readAsDataURL(file);

                if (searchForm) {
                    searchForm.action = 'visualsearch.php';
                    searchForm.method = 'POST';
                    setTimeout(() => {
                        searchForm.requestSubmit ? searchForm.requestSubmit() : searchForm.submit();
                    }, 150);
                }
            }
        });

        if (removeImgBtn) {
            removeImgBtn.addEventListener('click', (e) => {
                e.preventDefault();
                imageSearchInput.value = '';
                if (previewContainer) previewContainer.style.display = 'none';
                if (previewThumb) previewThumb.src = '';
                if (searchForm) {
                    searchForm.action = 'index.php';
                    searchForm.method = 'GET';
                }
            });
        }
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            if (imageSearchInput && imageSearchInput.files && imageSearchInput.files.length > 0) {
                searchForm.action = 'visualsearch.php';
                searchForm.method = 'POST';
            } else {
                searchForm.action = 'index.php';
                searchForm.method = 'GET';
            }
        });
    }

    // -------------------------------------------------------------
    // 3. SPEECH RECOGNITION (VOICE SEARCH) - FIXED & REAL-TIME
    // -------------------------------------------------------------
    const micBtn = document.getElementById('micBtn');
    const searchInput = document.getElementById('searchInput');

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (micBtn && searchInput) {
        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = true; // Live spoken words show honge
            recognition.lang = 'en-US'; // Standard English (Urdu ke liye 'ur-PK' use kar sakte hain)

            let isListening = false;
            let autoSubmitTimer = null;

            micBtn.addEventListener('click', (e) => {
                e.preventDefault();

                if (isListening) {
                    recognition.stop();
                } else {
                    try {
                        recognition.start();
                    } catch (err) {
                        console.error("Voice recognition start error:", err);
                    }
                }
            });

            recognition.onstart = () => {
                isListening = true;
                micBtn.classList.add('listening');
                searchInput.value = ''; // Nayi recording ke waqt purana input reset karein
                searchInput.placeholder = "Listening... Speak now...";
            };

            recognition.onresult = (event) => {
                let currentTranscript = '';
                
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    currentTranscript += event.results[i][0].transcript;
                }

                // Field me real-time typing preview show hoga
                searchInput.value = currentTranscript;

                // Speech completion check
                if (event.results[0].isFinal) {
                    micBtn.classList.remove('listening');
                    
                    if (searchForm) {
                        searchForm.action = 'index.php';
                        searchForm.method = 'GET';
                        
                        // Text reader visible rakhne ke liye 1.5s delay
                        clearTimeout(autoSubmitTimer);
                        autoSubmitTimer = setTimeout(() => {
                            if (searchInput.value.trim() !== '') {
                                searchForm.requestSubmit ? searchForm.requestSubmit() : searchForm.submit();
                            }
                        }, 1500);
                    }
                }
            };

            recognition.onerror = (event) => {
                isListening = false;
                micBtn.classList.remove('listening');
                searchInput.placeholder = "Search Anything...";
                console.error("Speech Recognition Error:", event.error);
                
                if (event.error === 'not-allowed') {
                    alert("Please allow Microphone Permission in your browser address bar!");
                }
            };

            recognition.onend = () => {
                isListening = false;
                micBtn.classList.remove('listening');
                if (!searchInput.value) {
                    searchInput.placeholder = "Search Anything...";
                }
            };
        } else {
            console.warn("Speech Recognition not supported in this browser.");
            micBtn.style.display = 'none';
        }
    }

    // -------------------------------------------------------------
    // 4. SHOW / HIDE CATEGORIES DROPDOWN SIDEBAR
    // -------------------------------------------------------------
    const catBtn = document.getElementById('catToggleBtn') || document.querySelector('.btn-category-orange') || document.querySelector('.category-btn');
    const catSidebar = document.getElementById('catSidebar') || document.getElementById('sidebar') || document.querySelector('.sidebar');

    if (catBtn) {
        catBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (catSidebar) {
                catSidebar.classList.toggle('active');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (catSidebar && catBtn) {
            if (!catSidebar.contains(e.target) && !catBtn.contains(e.target)) {
                catSidebar.classList.remove('active');
            }
        }
    });

    // -------------------------------------------------------------
    // 5. PRODUCT GALLERY INITIALIZATION & HOVER RESET
    // -------------------------------------------------------------
    const mainImg = document.getElementById('mainProductImg');
    const thumbGrid = document.querySelector('.thumbnail-grid');
    
    if (mainImg) {
        currentActiveSrc = mainImg.src;
    }

    if (thumbGrid && mainImg) {
        thumbGrid.addEventListener('mouseleave', function() {
            if (currentActiveSrc) {
                mainImg.src = currentActiveSrc;
            }
        });
    }

    // -------------------------------------------------------------
    // 6. DYNAMIC CUSTOM DROPDOWN HANDLING
    // -------------------------------------------------------------
    document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
        const trigger = dropdown.querySelector('.custom-select-trigger');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const options = dropdown.querySelectorAll('.custom-option');

        if (trigger) {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.custom-dropdown').forEach(d => {
                    if (d !== dropdown) d.classList.remove('open');
                });
                dropdown.classList.toggle('open');
            });
        }

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                options.forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                
                const selectedVal = opt.dataset.value;
                if (trigger && trigger.querySelector('span')) {
                    trigger.querySelector('span').innerText = selectedVal ? selectedVal : '-- Choose --';
                }
                if (hiddenInput) hiddenInput.value = selectedVal;
                
                dropdown.classList.remove('open');
            });
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown').forEach(d => d.classList.remove('open'));
    });

});

// Global functions
function previewImage(element) {
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg && element && element.src) {
        mainImg.src = element.src;
    }
}

function setActiveImage(element) {
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg && element && element.src) {
        currentActiveSrc = element.src;
        mainImg.src = element.src;
    }
    
    const thumbs = document.querySelectorAll('.thumbnail-grid .thumb, .thumb-img');
    thumbs.forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}

function resetImagePreview() {
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg && currentActiveSrc) {
        mainImg.src = currentActiveSrc;
    }
}

function toggleProducts() {
    const extraProducts = document.querySelectorAll('.extra-product');
    const toggleBtn = document.getElementById('toggleProductsBtn');
    
    if (!extraProducts.length || !toggleBtn) return;

    const isHidden = extraProducts[0].classList.contains('hidden');

    extraProducts.forEach(product => {
        if (isHidden) {
            product.classList.remove('hidden');
        } else {
            product.classList.add('hidden');
        }
    });

    if (isHidden) {
        toggleBtn.innerHTML = '<span>See Less</span> <span class="btn-arrow">↑</span>';
    } else {
        toggleBtn.innerHTML = '<span>See More Products</span> <span class="btn-arrow">↓</span>';
        const recContainer = document.querySelector('.recommendations-container');
        if (recContainer) {
            recContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

// -------------------------------------------------------------
// 7. PASSWORD SHOW / HIDE TOGGLE FUNCTION
// -------------------------------------------------------------
function togglePasswordVisibility(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
/* ==========================================================================
   CART QUANTITY CONTROLS
   ========================================================================== */

function increaseQty(cartId) {
    var input = document.getElementById('qty_' + cartId);
    if (input) {
        var currentValue = parseInt(input.value) || 1;
        input.value = currentValue + 1;
    }
}

function decreaseQty(cartId) {
    var input = document.getElementById('qty_' + cartId);
    if (input) {
        var currentValue = parseInt(input.value) || 1;
        if (currentValue > 1) {
            input.value = currentValue - 1;
        }
    }
}
/* ==========================================================================
   CHECKOUT FORM VALIDATION & HANDLING
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
    var checkoutForm = document.getElementById('checkoutForm');
    var btnPlaceOrder = document.getElementById('btnPlaceOrder');

    if (checkoutForm && btnPlaceOrder) {
        checkoutForm.addEventListener('submit', function () {
            // Prevent double submission
            btnPlaceOrder.disabled = true;
            btnPlaceOrder.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing Order...';
        });
    }
});