import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BridgeStep from "./BridgeStep";

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

function debounce(fn, ms = 500) {
  let timeout;

  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), ms);
  };
}

function validateBackendData(data) {
  return LISTMONK_HEADERS.reduce(
    (isValid, field) => {
      return isValid && data.headers[field];
    },
    /https?\:\/\/[^\/]+\.\w\w+/.test(data.base_url)
  );
}
export default function ListmonkTemplateWizard({ integration, onDone }) {
  const [{ backends }] = useGeneral();
  const [data, setData] = useState({});
  const [lists, setLists] = useState([]);

  const backendData = useMemo(() => {
    if (!data.backend?.name) return;
    const backend = backends.find(({ name }) => name === data.backend.name);

    if (backend && validateBackendData(backend)) {
      return backend;
    }

    const backendData = {
      name: data.backend.name,
      base_url: data.backend.base_url,
      headers: LISTMONK_HEADERS.reduce(
        (headers, name) => ({
          ...headers,
          [name]: data.backend[name],
        }),
        {}
      ),
    };

    if (validateBackendData(backendData)) {
      return backendData;
    }
  }, [data.backend, backends]);

  const fetchLists = useRef(
    debounce((data) => {
      const backend = {
        name: data.name,
        base_url: data.base_url,
        headers: LISTMONK_HEADERS.map((header) => ({
          name: header,
          value: data.headers[header],
        })),
      };

      apiFetch({
        path: "forms-bridge/v1/listmonk/lists",
        method: "POST",
        data: backend,
      })
        .then(setLists)
        .catch(() => setLists([]));
    }, 500)
  ).current;

  useEffect(() => {
    if (!backendData) return;
    fetchLists(backendData);
  }, [backendData]);

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
