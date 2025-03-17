import { useApis } from "../../../../src/providers/Settings";

export default function useDolibarrApi() {
  const [{ dolibarr: api = { bridges: [], templates: [] } }, patch] = useApis();
  const setApi = (data) => patch({ dolibarr: data });
  return [api, setApi];
}
