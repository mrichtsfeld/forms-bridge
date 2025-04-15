import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BridgeStep from "./BridgeStep";
import { useTemplateConfig } from "../../../../src/providers/Templates";
import { debounce, validateUrl } from "../../../../src/lib/utils";

const apiFetch = wp.apiFetch;
const { useState } = wp.element;

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
  if (!data?.name) return;

  return (
    validateUrl(data.base_url) &&
    data.headers.find(({ name }) => name === "DOLAPIKEY")?.value
  );
}

export default function DolibarrTemplateWizard({ integration, onDone }) {
  const [{ backends }] = useGeneral();

  const config = useTemplateConfig();
  const configFields = useMemo(() => config?.fields || [], [config]);
  const customFields = useMemo(() => {
    return configFields
      .filter((field) => field.ref === "#bridge/custom_fields[]")
      .map((field) => field.name);
  }, [configFields]);

  const [data, setData] = useState({});

  const [users, setUsers] = useState([]);

  const backend = useMemo(() => {
    if (!data.backend?.name) return;

    let backend = backends.find(({ name }) => name === data.backend.name);
    if (validateBackend(backend)) {
      return backend;
    }

    backend = {
      name: data.backend.name,
      base_url: data.backend.base_url,
      headers: [
        {
          name: "DOLAPIKEY",
          value: data.backend.DOLAPIKEY,
        },
      ],
    };

    if (validateBackend(backend)) {
      return backend;
    }
  });

  const fetch = useRef((module, then, backend) => {
    apiFetch({
      path: `forms-bridge/v1/dolibarr/${module}`,
      method: "POST",
      data: backend,
    })
      .then(then)
      .catch(() => then([]));
  }).current;

  const fetchUsers = useRef(
    debounce((backend) => fetch("users", setUsers, backend), 1e3)
  ).current;

  useEffect(() => {
    if (!backend) return;

    customFields.includes("userownerid") && fetchUsers(backend);
  }, [backend, customFields]);

  useEffect(() => {
    setData({
      ...data,
      bridge: {
        ...(data.bridge || {}),
        _users: users,
      },
    });
  }, [users]);

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
