import useDolibarrApi from "./useDolibarrApi";

const { useMemo } = wp.element;

export default function useApiKeyNames() {
  const [{ api_keys }] = useDolibarrApi();
  return useMemo(() => new Set(api_keys.map(({ name }) => name)), [api_keys]);
}
