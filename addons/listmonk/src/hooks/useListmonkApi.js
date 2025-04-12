import { useApis } from "../../../../src/providers/Settings";

export default function useListmonkApi() {
  const [
    { listmonk: api = { bridges: [], templates: [], workflow_jobs: [] } },
    patch,
  ] = useApis();
  const setApi = (data) => patch({ listmonk: data });
  return [api, setApi];
}
