// Note: This debounce is a global Vue mixin, but not yet
// available to directives here. Need to figure out how to do that
const debounce = (delay, fn) => {
  let timerId;
  return (...args) => {
    if (timerId) {
      clearTimeout(timerId);
    }
    timerId = setTimeout(() => {
      fn(...args);
      timerId = null;
    }, delay);
  };
};

const mobileCheck = {
  android: () => navigator.userAgent.match(/Android/i),
  blackberry: () => navigator.userAgent.match(/BlackBerry/i),
  ios: () => navigator.userAgent.match(/iPhone|iPad|iPod/i),
  opera: () => navigator.userAgent.match(/Opera Mini/i),
  windows: () => navigator.userAgent.match(/IEMobile/i),
  webos: () => navigator.userAgent.match(/webOS/i),
  any: () => (mobileCheck.android() || mobileCheck.blackberry()
    || mobileCheck.ios() || mobileCheck.opera() || mobileCheck.windows()),
};

// when below a certain threshold (1024 by default),
// force element to be as high as it is wide.
// If limit is set to zero, it'll be square everywhere.
// Set limit via: v-square:limit="768"
export const square = {
  bind(el, binding, vnode) {
    vnode.context.$nextTick(() => {
      const limit = (binding.expression) ? parseInt(binding.expression, 0) : 1024;
      // Android's window.innerWidth isn't consistent after orientation change
      const windowWidth = (mobileCheck.android) ? window.screen.width : window.innerWidth;

      const resizeElement = () => {
        if (windowWidth < limit || limit === 0) {
          el.style.height = `${el.offsetWidth}px`;
          el.classList.add('u-v-square');
        } else {
          el.style.height = 'auto';
          el.classList.remove('u-v-square');
        }
      };

      resizeElement();

      window.addEventListener('resize', debounce(100, () => {
        resizeElement();
      }));
    });
  },
};

// when below a certain threshold (1024 by default),
// force element to be as high as it is wide. Plus, use margins to extend the element full-width
// If limit is set to zero, it'll be square and full-width everywhere.
// Set limit via: v-square-full-width:limit="768"
export const squareFullWidth = {
  bind(el, binding, vnode) {
    vnode.context.$nextTick(() => {
      const limit = (binding.expression) ? parseInt(binding.expression, 0) : 1024;
      const resizeElement = () => {
        // Android's window.innerWidth isn't consistent after orientation change
        const windowWidth = (mobileCheck.android) ? window.screen.width : window.innerWidth;
        if (windowWidth < limit || limit === 0) {
          el.classList.add('u-v-square-full-width');
          el.style.width = `${windowWidth}px`;
          el.style.height = `${windowWidth}px`;
        } else {
          el.classList.remove('u-v-square-full-width');
          el.style.width = '100%';
          el.style.height = 'auto';
        }
      };

      resizeElement();

      window.addEventListener('resize', debounce(100, () => {
        resizeElement();
      }));
    });
  },
};
