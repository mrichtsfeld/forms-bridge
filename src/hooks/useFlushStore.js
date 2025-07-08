import { useForms } from "../providers/Forms";
import { useSettings } from "../providers/Settings";

const { useCallback } = wp.element;

export default function useFlushStore() {
  const [, fetchForms] = useForms();
  const [settings, submitSettings] = useSettings();

  return useCallback(
    () => fetchForms().then(() => submitSettings(settings)),
    [settings]
  );
}
