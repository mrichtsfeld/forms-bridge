const { Button } = wp.components;
const { __ } = wp.i18n;

export default function WorkflowEditor({ index, close }) {
  const save = () => {
    close();
  };

  return (
    <>
      <p
        style={{
          marginTop: "-3rem",
          position: "absolute",
          zIndex: 1,
        }}
      >
        {__("Workflow job editor", "forms-bridge")}
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
          <Button variant="primary" onClick={save}>
            {__("Save", "forms-bridge")}
          </Button>
        </div>
        <div
          style={{
            flex: 2,
            display: "flex",
            flexDirection: "column",
            height: "100%",
          }}
        ></div>
      </div>
    </>
  );
}
