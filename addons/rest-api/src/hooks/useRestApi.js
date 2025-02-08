import { useApis } from "../../../../src/providers/Settings";

export default function useRestApi() {
  const [{ "rest-api": api = { form_hooks: [] } }, patch] = useApis();
  const setApi = (value) => patch({ "rest-api": value });
  return [api, setApi];
}
