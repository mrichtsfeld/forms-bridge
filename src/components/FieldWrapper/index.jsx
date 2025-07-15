export default function FieldWrapper({ children, style = {} }) {
  return (
    <div style={{ width: "clamp(200px, 15vw, 300px)", ...style }}>
      {children}
    </div>
  );
}
