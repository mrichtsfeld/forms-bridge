import { useApis } from "../../../../src/providers/Settings";

export default function useHoldedApi() {
  const [
    { holded: api = { bridges: [], templates: [], workflow_jobs: [] } },
    patch,
  ] = useApis();
  const setApi = (data) => patch({ holded: data });
  return [api, setApi];
}
