import { useApis } from "../../../../src/providers/Settings";

export default function useRestApi() {
  const [{ zoho: api = { bridges: [], templates: [] } }, patch] = useApis();
  const setApi = (data) => patch({ zoho: data });
  return [api, setApi];
}
