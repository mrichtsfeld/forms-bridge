import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BridgeStep from "./BridgeStep";
import { debounce, validateUrl } from "../../../../src/lib/utils";
import { useTemplateConfig } from "../../../../src/providers/Templates";

const apiFetch = wp.apiFetch;
const { useState, useEffect, useMemo, useRef } = wp.element;

const LISTMONK_HEADERS = ["api_user", "token"];

const STEPS = [
  {
    name: "bridge",
    step: ({ fields, data, setData }) => (
      <BridgeStep fields={fields} data={data} setData={setData} />
    ),
    order: 20,
  },
];

function validateBackend(data) {
  if (!data?.name) return false;

  return LISTMONK_HEADERS.reduce((isValid, field) => {
    return isValid && data.headers.find(({ name }) => name === field)?.value;
  }, validateUrl(data.base_url));
}

export default function ListmonkTemplateWizard({ integration, onDone }) {
  const [{ backends }] = useGeneral();

  const config = useTemplateConfig();
  const configFields = useMemo(() => config?.fields || [], [config]);
  const customFields = useMemo(() => {
    return configFields
      .filter((field) => field.ref === "#bridge/custom_fields[]")
      .map((field) => field.name);
  }, [configFields]);

  const [data, setData] = useState({});

  const [lists, setLists] = useState([]);

  const backend = useMemo(() => {
    if (!data.backend?.name) return;

    let backend = backends.find(({ name }) => name === data.backend.name);
    if (backend && validateBackend(backend)) {
      return backend;
    }

    backend = {
      name: data.backend.name,
      base_url: data.backend.base_url,
      headers: LISTMONK_HEADERS.map((header) => ({
        name: header,
        value: data.backend[header],
      })),
    };

    if (validateBackend(backend)) {
      return backend;
    }
  }, [data.backend, backends]);

  const fetch = useRef((module, then, backend) => {
    apiFetch({
      path: `forms-bridge/v1/listmonk/${module}`,
      method: "POST",
      data: backend,
    })
      .then(then)
      .catch(() => then([]));
  }).current;

  const fetchLists = useRef(
    debounce((backend) => fetch("lists", setLists, backend), 1e3)
  ).current;

  useEffect(() => {
    if (!backend) return;

    customFields.includes("lists") && fetchLists(backend);
  }, [backend, customFields]);

  useEffect(
    () =>
      setData({
        ...data,
        bridge: { ...(data.bridge || {}), _lists: lists },
      }),
    [lists]
  );

  return (
    <TemplateWizard
      integration={integration}
      data={data}
      setData={setData}
      onDone={onDone}
      steps={STEPS}
    />
  );
}
