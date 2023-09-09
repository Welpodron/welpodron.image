"use strict";
((window) => {
    //!Original: https://github.com/verlok/vanilla-lazyload
    if (!window.welpodron) {
        window.welpodron = {};
    }
    if (!window.welpodron.lz) {
        window.welpodron.lz = {};
    }
    const MODULE_BASE = "lz";
    const ATTRIBUTE_BASE = `data-w-${MODULE_BASE}`;
    const ATTRIBUTE_BASE_SRC = `${ATTRIBUTE_BASE}-src`;
    const ATTRIBUTE_BASE_SRCSET = `${ATTRIBUTE_BASE}-srcset`;
    const ATTRIBUTE_BASE_BG = `${ATTRIBUTE_BASE}-bg`;
    const ATTRIBUTE_BASE_BG_MULTI = `${ATTRIBUTE_BASE}-bg-multi`;
    const isBot = typeof navigator !== "undefined" &&
        /(gle|ing|ro)bot|crawl|spider/i.test(navigator.userAgent);
    const setSourcesImg = (imgElement) => {
        if (imgElement.parentElement &&
            imgElement.parentElement.tagName === "PICTURE") {
            imgElement.parentElement
                .querySelectorAll("source")
                .forEach((sourceTag) => {
                sourceTag.setAttribute("srcset", sourceTag.getAttribute(ATTRIBUTE_BASE_SRCSET) ?? "");
            });
        }
        imgElement.setAttribute("src", imgElement.getAttribute(ATTRIBUTE_BASE_SRC) ?? "");
    };
    const load = (element) => {
        if (element.tagName === "IMG") {
            element.setAttribute(ATTRIBUTE_BASE, "applied");
            setSourcesImg(element);
        }
        else {
            element.setAttribute(ATTRIBUTE_BASE, "applied");
            const bgSingle = element.getAttribute(ATTRIBUTE_BASE_BG);
            const bgMultiple = element.getAttribute(ATTRIBUTE_BASE_BG_MULTI);
            if (bgMultiple) {
                element.style.backgroundImage = bgMultiple;
            }
            else if (bgSingle) {
                element.style.backgroundImage = `url("${bgSingle}")`;
            }
        }
    };
    const OBSERVER = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting || entry.intersectionRatio > 0) {
                observer.unobserve(entry.target);
                load(entry.target);
            }
        });
    }, {
        root: null,
        rootMargin: "300px",
    });
    const loadAll = () => {
        OBSERVER.disconnect();
        document
            .querySelectorAll(`[${ATTRIBUTE_BASE}]:not([${ATTRIBUTE_BASE}="applied"])`)
            .forEach((element) => {
            load(element);
        });
    };
    const update = () => {
        OBSERVER.disconnect();
        if (isBot) {
            loadAll();
            return;
        }
        document
            .querySelectorAll(`[${ATTRIBUTE_BASE}]:not([${ATTRIBUTE_BASE}="applied"])`)
            .forEach((element) => {
            OBSERVER.observe(element);
        });
    };
    window.welpodron.lz.observer = OBSERVER;
    window.welpodron.lz.update = update;
    window.welpodron.lz.load = load;
})(window);
//# sourceMappingURL=script.js.map