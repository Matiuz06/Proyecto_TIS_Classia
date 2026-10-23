(() => {
    const initAuth = () => {
        const container = document.getElementById('auth-container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        if (!container || !registerBtn || !loginBtn) {
            return;
        }

        const signIn = container.querySelector('.sign-in');
        const signUp = container.querySelector('.sign-up');

        const setMode = (mode, updateUrl = true) => {
            const isRegister = mode === 'registro';

            container.classList.toggle('active', isRegister);

            if (signIn) {
                signIn.setAttribute('aria-hidden', isRegister ? 'true' : 'false');
            }

            if (signUp) {
                signUp.setAttribute('aria-hidden', isRegister ? 'false' : 'true');
            }

            if (updateUrl && window.history.replaceState) {
                const url = new URL(window.location.href);

                if (isRegister) {
                    url.searchParams.set('modo', 'registro');
                    url.searchParams.delete('registro');
                } else {
                    url.searchParams.delete('modo');
                    url.searchParams.delete('registro');
                }

                window.history.replaceState({}, '', url.toString());
            }
        };

        registerBtn.addEventListener('click', () => {
            setMode('registro');
        });

        loginBtn.addEventListener('click', () => {
            setMode('login');
        });

        const initialMode =
            container.dataset.initialMode === 'registro'
                ? 'registro'
                : 'login';

        setMode(initialMode, false);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAuth, { once: true });
    } else {
        initAuth();
    }
})();
