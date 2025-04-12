import { useApis } from "../../../../src/providers/Settings";

export default function useBiginApi() {
  const [
    {
      bigin: api = {
        credentials: [],
        bridges: [],
        templates: [],
        workflow_jobs: [],
      },
    },
    patch,
  ] = useApis();
  const setApi = (data) => patch({ bigin: data });
  return [api, setApi];
}
