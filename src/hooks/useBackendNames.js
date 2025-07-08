// source
import useBackends from "./useBackends";

const { useMemo } = wp.element;

export default function useBackendNames() {
  const [backends] = useBackends();

  return useMemo(() => {
    return new Set(backends.map(({ name }) => name));
  }, [backends]);
}
