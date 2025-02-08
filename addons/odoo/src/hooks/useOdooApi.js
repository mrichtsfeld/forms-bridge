import { useApis } from "../../../../src/providers/Settings";

export default function useOdooApi() {
  const [{ odoo: api = { databases: [], form_hooks: [] } }, patch] = useApis();
  const setApi = (value) => patch({ odoo: value });
  return [api, setApi];
}
