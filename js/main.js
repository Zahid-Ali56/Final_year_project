/* ==========================================================================
   BAZAARLY - CENTRAL MAIN JAVASCRIPT FILE
   ========================================================================== */

// Global variable jo product gallery ki main image ka original source store karti hai
let currentActiveSrc = '';

document.addEventListener("DOMContentLoaded", function () {

    // -------------------------------------------------------------
    // 1. HIDE / SHOW HEADER ON SCROLL (Smart Navbar Hide/Show)
    // -------------------------------------------------------------
    
    let lastScrollTop = 0;
    const header = document.getElementById('mainHeader') || document.querySelector('header') || document.querySelector('.navbar');
    const delta = 5; // Scroll detection threshold / sensitivity

    if (header) {
        window.addEventListener('scroll', function() {
            let st = window.pageYOffset || document.documentElement.scrollTop;

            // Page top par hone par header show rakhein
            if (st <= 10) {
                header.classList.remove('nav-up');
                           return;
            }

            // Chote scrolls ignore karne ke liye delta check
            if (Math.abs(lastScrollTop - st) <= delta) return;

            // Downward Scroll: Header hide karein
            if (st > lastScrollTop && st > header.offsetHeight) {
                header.classList.add('nav-up');
            } else {
                // Upward Scroll: Header show karein
                if (st + window.innerHeight < document.documentElement.scrollHeight) {
                    header.classList.remove('nav-up');
                }
            }

            lastScrollTop = st;
        });
    }

    // -------------------------------------------------------------
    // 2. IMAGE SEARCH & DYNAMIC FORM SWITCHING LOGIC (Visual Search)
    // -------------------------------------------------------------
    // Explanation for Teacher: "Yeh module Image Search ko handle karta hai. 
    // User camera icon par click kare toh hidden file input trigger hota hai. 
    // FileReader API se instant image preview dikhta hai aur form Python/PHP AI endpoint par POST hota hai."

    const cameraBtn = document.getElementById('cameraBtn');
    const imageSearchInput = document.getElementById('imageSearchInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewThumb = document.getElementById('imagePreviewThumb');
    const removeImgBtn = document.getElementById('removeImgBtn');
    const searchForm = document.getElementById('searchForm');

    if (cameraBtn && imageSearchInput) {
        // Camera Button Click Handler
        cameraBtn.addEventListener('click', (e) => {
            e.preventDefault();
            imageSearchInput.click(); // Hidden input file browser window open karega
        });

        // File Select Event Listener
        imageSearchInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewThumb && previewContainer) {
                        previewThumb.src = e.target.result; // Dynamic base64 preview set karna
                        previewContainer.style.display = 'inline-flex';
                    }
                };
                reader.readAsDataURL(file);

                // Image Upload hote hi Form dynamic mode switch karta hai -> visualsearch.php
                if (searchForm) {
                    searchForm.action = 'visualsearch.php';
                    searchForm.method = 'POST';
                    setTimeout(() => {
                        searchForm.requestSubmit ? searchForm.requestSubmit() : searchForm.submit();
                    }, 150);
                }
            }
        });

        // Image Remove / Cancel Button Event Handler
        if (removeImgBtn) {
            removeImgBtn.addEventListener('click', (e) => {
                e.preventDefault();
                imageSearchInput.value = ''; // File clear karna
                if (previewContainer) previewContainer.style.display = 'none';
                if (previewThumb) previewThumb.src = '';
                
                // Form ko normal Text Search Mode (index.php GET) me revert karna
                if (searchForm) {
                    searchForm.action = 'index.php';
                    searchForm.method = 'GET';
                }
            });
        }
    }

    // Form Submit Routing (Safety Check)
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            // Agar image attached hai toh Visual Search (POST), warna Normal Search (GET)
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
    // Explanation "Yeh Web Speech API integration hai. 
    // Browser ka Native Speech Recognition engine use karta hai.
    // Interim results se screen par live word typing hoti hai aur speech stop hote hi auto-submit hota hai."

    const micBtn = document.getElementById('micBtn');
    const searchInput = document.getElementById('searchInput');

    // Cross-Browser Support Check (Standard SpeechRecognition or webkit SpeechRecognition)
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (micBtn && searchInput) {
        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            recognition.continuous = false; // Single query capture
            recognition.interimResults = true; // Live typing enable karna
            recognition.lang = 'en-US';

            let isListening = false;
            let autoSubmitTimer = null;

            // Mic Toggle Handler (Start/Stop Speech Engine)
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

            // Speech Start Event Listener
            recognition.onstart = () => {
                isListening = true;
                micBtn.classList.add('listening'); // Active UI animation state
                searchInput.value = ''; 
                searchInput.placeholder = "Listening... Speak now...";
            };

            // Voice Speech-to-Text Processing Event
            recognition.onresult = (event) => {
                let currentTranscript = '';
                
                // Spoken result process karna
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    currentTranscript += event.results[i][0].transcript;
                }

                // Sentence cleanup (end wale dot/question mark ko remove karna)
                let cleanedTranscript = currentTranscript.replace(/[\.\?]$/g, '').trim();

                // Live search bar inside value set karna
                searchInput.value = cleanedTranscript;

                // Final Voice Result Capture Event
                if (event.results[0].isFinal) {
                    micBtn.classList.remove('listening');
                    
                    if (searchForm) {
                        searchForm.action = 'index.php';
                        searchForm.method = 'GET';
                        
                        // 600ms Delay threshold user feedback clear visual sync ke liye
                        clearTimeout(autoSubmitTimer);
                        autoSubmitTimer = setTimeout(() => {
                            if (searchInput.value.trim() !== '') {
                                searchForm.requestSubmit ? searchForm.requestSubmit() : searchForm.submit();
                            }
                        }, 600);
                    }
                }
            };

            // Error Handler Event Listener
            recognition.onerror = (event) => {
                isListening = false;
                micBtn.classList.remove('listening');
                searchInput.placeholder = "Search Anything...";
                console.error("Speech Recognition Error:", event.error);
                
                if (event.error === 'not-allowed') {
                    alert("Please allow Microphone Permission in your browser address bar!");
                }
            };

            // Mic End Event Handler
            recognition.onend = () => {
                isListening = false;
                micBtn.classList.remove('listening');
                if (!searchInput.value) {
                    searchInput.placeholder = "Search Anything...";
                }
            };
        } else {
            console.warn("Speech Recognition not supported in this browser.");
            micBtn.style.display = 'none'; // Unsupported browsers me mic icon hide karna
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

    // Outside Click Event Listener: Sidebar auto-close logic
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
    // Explanation: "Product detail page gallery functionality. Multi-thumbnail images pe hover/click preview handle hota hai."

    const mainImg = document.getElementById('mainProductImg');
    const thumbGrid = document.querySelector('.thumbnail-grid');
    
    if (mainImg) {
        currentActiveSrc = mainImg.src; // Main active image path save karna
    }

    if (thumbGrid && mainImg) {
        // Thumbnail se cursor hatate hi selected main image revert karna
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
                if (hiddenInput) hiddenInput.value = selectedVal; // Hidden form field update karna
                
                dropdown.classList.remove('open');
            });
        });
    });

    // Dropdowns Global Close Event Listener
    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown').forEach(d => d.classList.remove('open'));
    });

});

// -------------------------------------------------------------
// GLOBAL UTILITY FUNCTIONS (Accessible from HTML Attributes)
// -------------------------------------------------------------

// Mouseover preview switch function
function previewImage(element) {
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg && element && element.src) {
        mainImg.src = element.src;
    }
}

// Click par permanent active image update function
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

// Preview Reset Function
function resetImagePreview() {
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg && currentActiveSrc) {
        mainImg.src = currentActiveSrc;
    }
}

// See More / See Less Products Toggle Function
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
// Explanation: "Form Security UI Feature. Eye icon click karne par password show /hide toggle hota hai."

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
   8. CART QUANTITY CONTROLS (Increment/Decrement Input Fields)
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
   9. CHECKOUT FORM VALIDATION & HANDLING
   ========================================================================== */
// Explanation : "Order Place hone ke doraan user multiple clicks na kare (Double Submission Prevention),
// #double click disable hi

document.addEventListener('DOMContentLoaded', function () {
    var checkoutForm = document.getElementById('checkoutForm');
    var btnPlaceOrder = document.getElementById('btnPlaceOrder');

    if (checkoutForm && btnPlaceOrder) {
        checkoutForm.addEventListener('submit', function () {
            btnPlaceOrder.disabled = true;
            btnPlaceOrder.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing Order...';
        });
    }
});