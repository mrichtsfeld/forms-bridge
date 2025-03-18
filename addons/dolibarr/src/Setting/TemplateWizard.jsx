import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BackendStep from "../../../../src/components/Templates/Steps/BackendStep";
import useApiKeyNames from "../hooks/useApiKeyNames";
import ApiKeyStep from "./ApiKeyStep";

const { useState, useMemo, useEffect, useRef } = wp.element;

export default function DolibarrTemplateWizard({ integration, onDone }) {
  const [{ backends }] = useGeneral();
  const apiKeyNames = useApiKeyNames();
  const [data, setData] = useState({});

  const defaultSteps = useRef([
    {
      name: "api_key",
      step: ({ fields, data, setData }) => (
        <ApiKeyStep fields={fields} data={data} setData={setData} />
      ),
      order: 0,
    },
    {
      name: "backend",
      step: ({ fields, data, setData }) => (
        <BackendStep fields={fields} data={data} setData={setData} />
      ),
      order: 5,
    },
  ]).current;

  const steps = useMemo(
    () =>
      defaultSteps.map(({ name, step, order }) => {
        if (
          name === "backend" &&
          data.api_key?.name &&
          apiKeyNames.has(data.api_key.name)
        ) {
          step = null;
        }

        return { name, step, order };
      }),
    [apiKeyNames, data.api_key?.name]
  );

  useEffect(() => {
    if (data.api_key?.name && apiKeyNames.has(data.api_key.name)) {
      const backend = backends.find(
        ({ name }) => name === data.api_key.backend
      );
      data.backend.name = backend.name;
      data.backend.base_url = backend.base_url;
    }
  }, [data.api_key?.name]);

  return (
    <TemplateWizard
      integration={integration}
      steps={steps}
      data={data}
      setData={setData}
      onDone={onDone}
    />
  );
}
