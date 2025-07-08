// source
import { useJobConfig } from "../../providers/Jobs";
import JobCodeEditor from "./CodeEditor";

const { useState, useEffect, useRef } = wp.element;
const { TabPanel, Button } = wp.components;
const { __ } = wp.i18n;

const EDITOR_TABS = [
  { title: __("Input", "forms-bridge"), name: "input" },
  { title: __("Output", "forms-bridge"), name: "output" },
  { title: __("Job", "forms-bridge"), name: "snippet" },
];

export default function JobEditor({ close }) {
  const [config, setConfig] = useJobConfig();
  const [state, setState] = useState();
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    setState(config);
  }, [config]);

  const save = useRef((config) => {
    setIsLoading(true);
    setConfig(config).finally(() => setIsLoading(false));
  }).current;

  if (!state?.id || isLoading) return;

  return (
    <div style={{ width: "100%", flex: 1 }}>
      <TabPanel tabs={EDITOR_TABS}>
        {(tab) => (
          <JobEditorContent
            tab={tab.name}
            job={state}
            update={(newState) => setState({ ...state, ...newState })}
          />
        )}
      </TabPanel>
      <div style={{ display: "flex", gap: "0.5em" }}>
        <Button variant="primary" onClick={() => save(state)}>
          {__("Save", "forms-bridge")}
        </Button>
        <Button variant="primary" isDestructive onClick={close}>
          {__("Discard", "forms-bridge")}
        </Button>
      </div>
    </div>
  );
}

function JobEditorContent({ tab, job, update }) {
  switch (tab) {
    case "input":
      return "Input";
    case "output":
      return "Output";
    case "snippet":
      return (
        <JobCodeEditor
          id={job.id}
          doc={job.snippet}
          update={(snippet) => update({ ...job, snippet })}
        />
      );
  }
}
