import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BridgeStep from "./BridgeStep";
import { useTemplateConfig } from "../../../../src/providers/Templates";
import { debounce } from "../../../../src/lib/utils";

const apiFetch = wp.apiFetch;
const { useState, useEffect, useMemo, useRef } = wp.element;

const MAILCHIMP_HEADERS = ["api-key", "datacenter"];

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

  return MAILCHIMP_HEADERS.reduce((isValid, field) => {
    return isValid && data.headers.find(({ name }) => name === field)?.value;
  }, true);
}

export default function MailchimpTemplateWizard({ integration, onDone }) {
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
    if (validateBackend(backend)) {
      return backend;
    }

    backend = {
      name: data.backend.name,
      headers: MAILCHIMP_HEADERS.map((header) => ({
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
      path: `forms-bridge/v1/mailchimp/${module}`,
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

    customFields.includes("list_id") && fetchLists(backend);
  }, [backend, customFields]);

  useEffect(() => {
    setData({
      ...data,
      bridge: {
        ...(data.bridge || {}),
        _lists: lists,
      },
    });
  }, [lists]);

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
