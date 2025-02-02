// source
import { useFormHooks } from "../providers/Settings";

const { useMemo } = wp.element;

export default function useHookNames() {
  const formHooks = useFormHooks();

  return useMemo(() => {
    return new Set(formHooks.map(({ name }) => name));
  }, [formHooks]);
}
