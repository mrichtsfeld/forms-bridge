import { useApis } from "../../../../src/providers/Settings";

export default function useRestApi() {
  const [{ financoop: api = { form_hooks: [] } }, patch] = useApis();
  const setApi = (value) => patch({ financoop: value });
  return [api, setApi];
}
