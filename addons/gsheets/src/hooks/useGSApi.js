// source
import { useApis } from "../../../../src/providers/Settings";

export default function useGSApi() {
  const [
    {
      gsheets: api = {
        authorized: false,
        bridges: [],
        templates: [],
        workflow_jobs: [],
      },
    },
    patch,
  ] = useApis();

  const setApi = (data) => patch({ gsheets: data });

  return [api, setApi];
}
