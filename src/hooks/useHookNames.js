// vendor
import { useMemo } from "@wordpress/element";

// source
import { useFormHooks } from "../providers/Settings";

export default function useHookNames() {
  const formHooks = useFormHooks();

  return useMemo(() => {
    return new Set(formHooks.map(({ name }) => name));
  }, [formHooks]);
}
