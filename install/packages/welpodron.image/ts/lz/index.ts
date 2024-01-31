const MODULE_BASE = 'lz';

const ATTRIBUTE_BASE = `data-w-${MODULE_BASE}`;
const ATTRIBUTE_BASE_SRC = `${ATTRIBUTE_BASE}-src`;
const ATTRIBUTE_BASE_SRCSET = `${ATTRIBUTE_BASE}-srcset`;
const ATTRIBUTE_BASE_BG = `${ATTRIBUTE_BASE}-bg`;
const ATTRIBUTE_BASE_BG_MULTI = `${ATTRIBUTE_BASE}-bg-multi`;

class Lz {
  observer?: IntersectionObserver;

  isBot =
    typeof navigator !== 'undefined' &&
    /(gle|ing|ro)bot|crawl|spider/i.test(navigator.userAgent);

  constructor() {
    if ((Lz as any).instance) {
      return (Lz as any).instance;
    }

    if ('IntersectionObserver' in window) {
      this.observer = new IntersectionObserver(
        (entries, observer) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting || entry.intersectionRatio > 0) {
              observer.unobserve(entry.target);
              this.load(entry.target as HTMLElement);
            }
          });
        },
        {
          root: null,
          rootMargin: '300px',
        }
      );
    }
  }

  setSourcesImg = (imgElement: HTMLImageElement) => {
    if (
      imgElement.parentElement &&
      imgElement.parentElement.tagName === 'PICTURE'
    ) {
      imgElement.parentElement
        .querySelectorAll('source')
        .forEach((sourceTag) => {
          sourceTag.setAttribute(
            'srcset',
            sourceTag.getAttribute(ATTRIBUTE_BASE_SRCSET) ?? ''
          );
        });
    }
    imgElement.setAttribute(
      'src',
      imgElement.getAttribute(ATTRIBUTE_BASE_SRC) ?? ''
    );
  };

  load = (element: HTMLElement) => {
    if (element.tagName === 'IMG') {
      element.setAttribute(ATTRIBUTE_BASE, 'applied');
      this.setSourcesImg(element as HTMLImageElement);
    } else {
      element.setAttribute(ATTRIBUTE_BASE, 'applied');

      const bgSingle = element.getAttribute(ATTRIBUTE_BASE_BG);
      const bgMultiple = element.getAttribute(ATTRIBUTE_BASE_BG_MULTI);

      if (bgMultiple) {
        element.style.backgroundImage = bgMultiple;
      } else if (bgSingle) {
        element.style.backgroundImage = `url("${bgSingle}")`;
      }
    }
  };

  loadAll = () => {
    if (!this.observer) {
      return;
    }

    this.observer.disconnect();

    document
      .querySelectorAll(
        `[${ATTRIBUTE_BASE}]:not([${ATTRIBUTE_BASE}="applied"])`
      )
      .forEach((element) => {
        this.load(element as HTMLElement);
      });
  };

  update = () => {
    if (this.isBot) {
      return this.loadAll();
    }

    if (!this.observer) {
      return;
    }

    this.observer.disconnect();

    document
      .querySelectorAll(
        `[${ATTRIBUTE_BASE}]:not([${ATTRIBUTE_BASE}="applied"])`
      )
      .forEach((element) => {
        this.observer?.observe(element);
      });
  };
}

export { Lz as lz };
