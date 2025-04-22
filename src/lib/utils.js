export function debounce(fn, ms = 500) {
  let timeout;

  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), ms);
  };
}

export function validateUrl(url) {
  try {
    url = new URL(url);
  } catch {
    return false;
  }

  return url.protocol === "http:" || url.protocol === "https:";
}

export function sortByNamesOrder(items, order) {
  return items.sort((a, b) => {
    if (!order.includes(a.name)) {
      return 1;
    } else if (!order.includes(b.name)) {
      return -1;
    } else {
      return order.indexOf(a.name) - order.indexOf(b.name);
    }
  });
}

export function prependEmptyOption(options) {
  return [{ label: "", value: "" }].concat(options);
}
