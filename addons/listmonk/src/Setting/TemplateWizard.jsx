import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import ListmonkBridgeStep from "./BridgeStep";
import { debounce, validateUrl } from "../../../../src/lib/utils";
import { useTemplateConfig } from "../../../../src/providers/Templates";

const apiFetch = wp.apiFetch;
const { useState, useEffect, useMemo, useRef } = wp.element;

const STEPS = [
  {
    name: "bridge",
    component: ListmonkBridgeStep,
    order: 20,
  },
];

function validateBackend(data) {
  if (!data?.name) return false;

  const authorization =
    data.headers.find((h) => h.name === "Authorization")?.value || "";

  return validateUrl(data.base_url) && /^token .+\:.+$/.test(authorization);
}

export default function ListmonkTemplateWizard({
  integration,
  wired,
  setWired,
  onDone,
}) {
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

    const user = data.backend.user;
    const token = data.backend.token;
    if (!user || !token) {
      return;
    }

    const authorization = `token ${user}:${token}`;

    backend = {
      name: data.backend.name,
      base_url: data.backend.base_url,
      headers: [
        { name: "Content-Type", value: "application/json" },
        { name: "Accept", value: "application/json" },
        { name: "Authorization", value: authorization },
      ],
    };

    if (validateBackend(backend)) {
      return backend;
    }
  }, [data.backend, backends]);

  const fetch = useRef((endpoint, then, backend) => {
    apiFetch({
      path: "forms-bridge/v1/listmonk/backend/api/fetch",
      method: "POST",
      data: { backend, endpoint },
    })
      .then(then)
      .catch(() => then([]));
  }).current;

  const fetchLists = useRef(
    debounce(
      (backend) =>
        fetch("/api/lists", (data) => setLists(data.data.results), backend),
      1e3
    )
  ).current;

  useEffect(() => {
    if (!backend || !wired) return;

    customFields.includes("lists") && fetchLists(backend);
  }, [wired, backend, customFields]);

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
      wired={wired}
      setWired={setWired}
      onDone={onDone}
      steps={STEPS}
    />
  );
}
