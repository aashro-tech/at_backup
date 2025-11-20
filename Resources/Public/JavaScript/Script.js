(function($){
    'use strict'
    $(document).ready(function(){

        // Modal Display
        const storageKey = 'show3GBWarning';
        const showModal = localStorage.getItem(storageKey) !== 'false';

        if (showModal) {
            $('#sizeLimitModal').css('display', 'flex');
        }

        $('#closeModal').on('click', function () {
            if ($('#dontShowAgain').is(':checked')) {
                localStorage.setItem(storageKey, 'false');
            }
            $('#sizeLimitModal').fadeOut();
        });

        
        if ($('.v12').length) {
            $('.at-datatable').dataTable({
                paginate: true,
                order: []
            });
        }

        // Tabs
        const tabs = document.querySelectorAll('.tab-btn');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(button => {
            button.addEventListener('click', function () {
                const target = this.getAttribute('data-tab');

                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                this.classList.add('active');
                document.getElementById(target).classList.add('active');
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = '';

        if ($('.v12').length) {
            activeTab = urlParams.get('activeTab') === "tab-history" ? "tab-history" : "null";
        } else {
            activeTab = urlParams.get('tx_atbackup_web_atbackupbackup[activeTab]') === "tab-history" ? "tab-history" : "null";
        }

        if (activeTab != 'null') {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            const button = document.querySelector(`.tab-btn[data-tab="${activeTab}"]`);
            const tabContent = document.getElementById(activeTab);

            if (button && tabContent) {
                button.classList.add('active');
                tabContent.classList.add('active');
            }
        }

        // Modal
        const modal = document.getElementById('deleteModal');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const cancelBtn = document.querySelector('.cancel-delete-modal');

        // Use event delegation to handle clicks on current and future .open-delete-modal elements
        document.addEventListener('click', function (e) {
            const target = e.target.closest('.open-delete-modal');
            if (target) {
                e.preventDefault();
                const url = target.getAttribute('data-delete-url');
                confirmBtn.setAttribute('href', url);
                modal.classList.add('show');
            }
        });

        // Cancel button to close modal
        cancelBtn.addEventListener('click', function () {
            modal.classList.remove('show');
        });

        // Loader while backup is running
        const form = document.querySelector('form');
        const loader = document.querySelector('.loader-overlay');

        form?.addEventListener('submit', function () {
            loader.style.display = 'flex';
        });

        // Dark Mode
        const $body = $('body');
        const $toggleButton = $('#toggleButton');
        const lightLabel = $toggleButton.data('light-label');
        const darkLabel = $toggleButton.data('dark-label');

        // Check sessionStorage for mode on page load
        const storedMode = sessionStorage.getItem('themeMode');
        if (storedMode === 'dark') {
            $body.addClass('dark-mode');
            $toggleButton.text('🌓 ' + lightLabel);
        } else {
            $body.removeClass('dark-mode');
            $toggleButton.text('🌓 ' + darkLabel);
        }

        // Toggle dark/light mode on button click
        $toggleButton.on('click', function () {
            $body.toggleClass('dark-mode');

            const isDarkMode = $body.hasClass('dark-mode');
            sessionStorage.setItem('themeMode', isDarkMode ? 'dark' : 'light');

            $toggleButton.text('🌓 ' + (isDarkMode ? lightLabel : darkLabel));
        });


        // Auto-hide flash messages after 5 seconds
        const flashMessages = document.querySelectorAll('.flash-messages');
        setTimeout(() => {
            flashMessages.forEach(msg => msg.style.display = 'none');
        }, 5000);

        // get Year
        document.getElementById("copyright").innerHTML = new Date().getFullYear();

    });
})(jQuery);