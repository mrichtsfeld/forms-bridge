// source
import WorkflowPipeline from "./WorkflowPipeline";

const { Button, Modal } = wp.components;
const { useState } = wp.element;
const { __ } = wp.i18n;

export default function BridgeWorkflow({ jobs, workflow = [], setWorkflow }) {
  const [open, setOpen] = useState(false);

  return (
    <>
      <Button
        variant={workflow.length ? "primary" : "secondary"}
        onClick={() => setOpen(true)}
        style={{ width: "150px", justifyContent: "center" }}
        __next40pxDefaultSize
      >
        {__("Workflow", "forms-bridge")}
      </Button>
      {open && (
        <Modal
          title={__("Bridge workflow", "forms-bridge")}
          onRequestClose={() => setOpen(false)}
        >
          <WorkflowPipeline
            workflow={workflow}
            setWorkflow={setWorkflow}
            jobs={jobs}
          />
        </Modal>
      )}
    </>
  );
}
