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
