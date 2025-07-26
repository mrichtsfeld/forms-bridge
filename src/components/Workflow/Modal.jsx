const { Modal } = wp.components;
const { __ } = wp.i18n;

export default function WorkflowModal({ show, onClose }) {
  if (!show) return;

  return (
    <Modal
      title={__("Submission workflow", "forms-bridge")}
      onRequestClose={onClose}
    >
      <WorkflowProvider
        backend={backend}
        form={form}
        mutations={mutations}
        workflow={workflow}
        customFields={customFields}
      >
        <p
          style={{
            marginTop: "-3rem",
            position: "absolute",
            zIndex: 1,
          }}
        >
          {__(
            "Process the form submission before it is sent to the backend over the bridge",
            "forms-bridge"
          )}
        </p>
        <div
          style={{
            marginTop: "2rem",
            width: "1280px",
            maxWidth: "80vw",
            height: "500px",
            maxHeight: "80vh",
            display: "flex",
          }}
        >
          <div
            style={{
              flex: 1,
              maxWidth: "400px",
              display: "flex",
              flexDirection: "column",
              height: "100%",
              borderRight: "1px solid",
              paddingRight: "1em",
              marginRight: "1em",
            }}
          >
            <WorkflowPipeline workflow={workflow} setWorkflow={setWorkflow} />
          </div>
          <div
            style={{
              flex: 2,
              display: "flex",
              flexDirection: "column",
              height: "100%",
            }}
          >
            <WorkflowStage
              setEdit={setEdit}
              setMappers={(step, mappers) => setMutationMappers(step, mappers)}
            />
          </div>
        </div>
      </WorkflowProvider>
    </Modal>
  );
}
