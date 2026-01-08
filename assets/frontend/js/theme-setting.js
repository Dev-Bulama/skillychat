(function () {
    "use strict"
    // HTML Root Element
    const rootHtml = document.documentElement;

    const languageRtlStates = window?.languageRtlStates || { "en": false };


    function setElementAttribute(element, attribute, value) {
        element.setAttribute(attribute, value);
    }

    // Default localStorage settings
    let siteData = {
        lang: "en",
        dir: "ltr",
        dataTheme: "light",
    };

    const setLocalStorageData = (siteData) => {
        localStorage.setItem("siteData", JSON.stringify(siteData))
    };

    function getLocalStorageData() {
        const siteDataJSON = localStorage.getItem("siteData");
        return siteDataJSON ? JSON.parse(siteDataJSON) : { lang: "en", dir: "ltr", dataTheme: "light" };
    }

    function getDirectionForLanguage(lang) {
        return languageRtlStates[lang] ? "rtl" : "ltr";
    }

    // Function to get current language from various sources
    function getCurrentLanguage() {
        const storedData = getLocalStorageData();
        if (storedData.lang) {
            return storedData.lang;
        }

        const htmlLang = document.documentElement.getAttribute('lang');
        if (htmlLang) {
            return htmlLang;
        }

        if (window.currentLanguage) {
            return window.currentLanguage;
        }

        return "en";
    }

    // LTR & RTL Features Start
    function updateBootstrapCSS(cssFile) {
        const existingLink = document.querySelector("#bootstrap-css");

        if (existingLink && existingLink.href !== cssFile) {
            // Create new link element
            const newLink = document.createElement("link");
            newLink.rel = "stylesheet";
            newLink.type = "text/css";
            newLink.href = cssFile;
            newLink.id = "bootstrap-css";
            newLink.nonce = "{{ csp_nonce() }}";

            // Replace the old link with the new one to avoid FOUC
            newLink.onload = function () {
                if (existingLink.parentNode) {
                    existingLink.parentNode.removeChild(existingLink);
                }

                requestAnimationFrame(() => {
                    window.dispatchEvent(new Event("resize"));
                });
            };

            existingLink.parentNode.insertBefore(newLink, existingLink);
        }
    }

    function setDirection(dirMode) {
        requestAnimationFrame(() => {
            rootHtml.setAttribute("dir", dirMode);

            // Check if window.assetUrls is available before using it
            if (window.assetUrls && window.assetUrls.bootstrapLtr && window.assetUrls.bootstrapRtl) {
                const cssFile = dirMode === "rtl"
                    ? window.assetUrls.bootstrapRtl
                    : window.assetUrls.bootstrapLtr;

                updateBootstrapCSS(cssFile);
            } else {
                console.warn('Asset URLs not available. Bootstrap CSS switching disabled.');
            }
        });
    }

    function handleDirection() {
        const currentDir = rootHtml.getAttribute("dir") || "ltr";
        const dirMode = currentDir === "ltr" ? "rtl" : "ltr";

        // Update icon
        const activeIcon = document.querySelector('#direction-switcher > i');
        if (activeIcon) {
            activeIcon.className = dirMode === "rtl" ? "bi bi-filter-right" : "bi bi-filter-left";
        }

        setDirection(dirMode);
        const currentData = { ...getLocalStorageData(), dir: dirMode };
        setLocalStorageData(currentData);
    }

    function handleLanguageChange(newLang) {
        const newDir = getDirectionForLanguage(newLang);
        setDirection(newDir);

        const currentData = { ...getLocalStorageData(), lang: newLang, dir: newDir };
        setLocalStorageData(currentData);

        rootHtml.setAttribute('lang', newLang);

        const activeIcon = document.querySelector('#direction-switcher > i');
        if (activeIcon) {
            activeIcon.className = newDir === "rtl" ? "bi bi-filter-right" : "bi bi-filter-left";
        }
    }

    function initializeDirectionSwitcher() {
        const switcher = document.querySelector('#direction-switcher');
        if (switcher) {

            const storedData = getLocalStorageData();
            const activeIcon = switcher.querySelector('i');
            if (activeIcon) {
                activeIcon.className = storedData.dir === "rtl" ? "bi bi-filter-right" : "bi bi-filter-left";
            }

            switcher.addEventListener('click', handleDirection);
        }
    }

    // Function to initialize direction based on current language
    function initializeLanguageBasedDirection() {
        const currentLang = getCurrentLanguage();
        const autoDir = getDirectionForLanguage(currentLang);
        const storedData = getLocalStorageData();

        // Check if stored direction matches language requirement
        if (storedData.dir !== autoDir || storedData.lang !== currentLang) {
            handleLanguageChange(currentLang);
        } else {
            // Just set the direction without updating storage
            setDirection(storedData.dir);
        }
    }

    // Function to listen for language changes (if you have language switcher)
    function initializeLanguageSwitcher() {
        // Listen for language switcher clicks (adjust selector as needed)
        const languageSwitchers = document.querySelectorAll('.language-switcher, [data-lang]');

        languageSwitchers.forEach(switcher => {
            switcher.addEventListener('click', function (e) {
                const newLang = this.getAttribute('data-lang') || this.getAttribute('href')?.split('/').pop();
                if (newLang && languageRtlStates.hasOwnProperty(newLang)) {
                    handleLanguageChange(newLang);
                }
            });
        });
    }



    // ===========Dark Mode Features Start====================

    const handleTheme = (e) => {
        const currentAttribute = rootHtml.getAttribute("data-bs-theme");
        const newAttribute = currentAttribute === 'light' ? 'dark' : 'light';

        setElementAttribute(rootHtml, "data-bs-theme", newAttribute);

        const activeIcon = document.querySelector('#theme-toggle > i');
        if (activeIcon) {
            // Update active icon based on theme
            activeIcon.className = newAttribute === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        }

        setThemePreference(newAttribute);
    };

    const getColorPreference = () => {
        const storedData = getLocalStorageData();
        if (storedData && storedData.dataTheme) {
            return storedData.dataTheme;
        } else {
            return window.matchMedia('(prefers-color-scheme: dark)').matches
                ? 'dark'
                : 'light';
        }
    };

    const setThemePreference = (theme) => {
        const currentData = { ...getLocalStorageData(), dataTheme: theme };
        setLocalStorageData(currentData);
        reflectPreference(theme);
    };

    const reflectPreference = (theme) => {
        rootHtml.setAttribute("data-bs-theme", theme);
        const themeToggle = document.querySelector('#theme-toggle');
        if (themeToggle) {
            themeToggle.setAttribute('aria-label', theme);
        }
    };

    // Theme toggle initialization
    function initializeThemeToggle() {
        const themeToggle = document.querySelector('#theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', handleTheme);
        }
    }

    // Sync with system changes
    function initializeSystemThemeSync() {
        window.matchMedia('(prefers-color-scheme: dark)')
            .addEventListener('change', ({ matches: isDark }) => {
                const currentTheme = isDark ? 'dark' : 'light';
                setThemePreference(currentTheme);
            });
    }

    // Initialize theme from stored preferences
    function initializeStoredTheme() {
        const storedData = getLocalStorageData();
        const currentTheme = storedData.dataTheme || getColorPreference();
        rootHtml.setAttribute("data-bs-theme", currentTheme);
        const activeIcon = document.querySelector('#theme-toggle > i');
        if (activeIcon) {
            activeIcon.className = currentTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        }
    }

    // Initialize all features
    function initializeAll() {
        // Wait for window.assetUrls to be available
        if (window.assetUrls) {
            initializeLanguageBasedDirection(); // Initialize direction based on language first
        } else {
            // If assetUrls not available yet, wait a bit and try again
            setTimeout(() => {
                initializeLanguageBasedDirection();
            }, 100);
        }

        initializeDirectionSwitcher();
        initializeLanguageSwitcher();
        initializeThemeToggle();
        initializeStoredTheme();
        initializeSystemThemeSync();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAll);
    } else {
        // DOM is already loaded
        initializeAll();
    }

}())