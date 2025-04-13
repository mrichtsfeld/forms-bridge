import { useApis } from "../../../../src/providers/Settings";

export default function useBrevoApi() {
  const [
    { brevo: api = { bridges: [], templates: [], workflow_jobs: [] } },
    patch,
  ] = useApis();
  const setApi = (data) => patch({ brevo: data });
  return [api, setApi];
}
