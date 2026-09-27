<script>
    lucide.createIcons();
</script>
<script>
    document.addEventListener('alpine:init', () => {

        Alpine.store('theme', {

            current: '{{ auth()->user()->theme ?? "theme-dark" }}',

            logoMode() {

                return {

                    'theme-dark': 'dark',

                    'theme-iracing': 'light',

                    'theme-monaco': 'dark',

                    'theme-monza': 'dark',

                    'theme-laguna': 'light',

                    'theme-hock': 'dark',

                    'theme-spa': 'dark',

                    'theme-crtg': 'dark',

                    'theme-light': 'light',

                    'theme-jerez': 'light',

                }[this.current] || 'dark';
            }
        });

    });
</script>
