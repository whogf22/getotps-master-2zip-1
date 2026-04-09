import { useRef, useMemo, Suspense } from "react";
import { Canvas, useFrame } from "@react-three/fiber";
import { Sphere, Float, Stars, MeshDistortMaterial, Ring } from "@react-three/drei";
import * as THREE from "three";
import { SceneErrorBoundary } from "./SceneErrorBoundary";

/* ── Earth-like globe with glowing points ── */
function OTPGlobe() {
  const globeRef = useRef<THREE.Mesh>(null);
  const wireRef = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (globeRef.current) globeRef.current.rotation.y = state.clock.elapsedTime * 0.08;
    if (wireRef.current) wireRef.current.rotation.y = state.clock.elapsedTime * 0.05;
  });

  return (
    <group>
      {/* Core globe */}
      <mesh ref={globeRef}>
        <sphereGeometry args={[2.0, 48, 48]} />
        <meshStandardMaterial
          color="#062030"
          emissive="#062030"
          emissiveIntensity={1}
          metalness={0.3}
          roughness={0.7}
        />
      </mesh>
      {/* Wireframe overlay */}
      <mesh ref={wireRef}>
        <sphereGeometry args={[2.02, 28, 28]} />
        <meshBasicMaterial color="#0ea5e9" wireframe transparent opacity={0.09} />
      </mesh>
      {/* Atmosphere glow ring */}
      <mesh>
        <sphereGeometry args={[2.18, 32, 32]} />
        <meshBasicMaterial color="#0ea5e9" transparent opacity={0.04} side={THREE.BackSide} />
      </mesh>
    </group>
  );
}

/* ── OTP code packet flying toward a phone ── */
function OTPPacket({ startAngle, speed, color }: { startAngle: number; speed: number; color: string }) {
  const mesh = useRef<THREE.Mesh>(null);
  const angle = useRef(startAngle);

  useFrame(() => {
    angle.current += speed * 0.01;
    const r = 2.5 + Math.sin(angle.current * 0.3) * 0.3;
    const x = Math.cos(angle.current) * r;
    const z = Math.sin(angle.current) * r;
    const y = Math.sin(angle.current * 0.7) * 1.2;
    if (mesh.current) {
      mesh.current.position.set(x, y, z);
      mesh.current.rotation.x += 0.02;
      mesh.current.rotation.y += 0.03;
    }
  });

  return (
    <mesh ref={mesh}>
      <boxGeometry args={[0.18, 0.10, 0.04]} />
      <meshStandardMaterial color={color} emissive={color} emissiveIntensity={0.9} metalness={0.5} roughness={0.1} />
    </mesh>
  );
}

/* ── Connection arcs between globe surface points ── */
function ConnectionArc({ from, to }: { from: [number, number, number]; to: [number, number, number] }) {
  const arcRef = useRef<THREE.Line>(null);
  const progress = useRef(0);

  const points = useMemo(() => {
    const pts = [];
    for (let i = 0; i <= 30; i++) {
      const t = i / 30;
      const x = from[0] + (to[0] - from[0]) * t;
      const y = from[1] + (to[1] - from[1]) * t + Math.sin(t * Math.PI) * 1.5;
      const z = from[2] + (to[2] - from[2]) * t;
      pts.push(new THREE.Vector3(x, y, z));
    }
    return pts;
  }, [from, to]);

  const geometry = useMemo(() => new THREE.BufferGeometry().setFromPoints(points), [points]);

  useFrame(() => {
    progress.current = (progress.current + 0.005) % 1;
    if (arcRef.current) {
      (arcRef.current.material as THREE.LineBasicMaterial).opacity =
        0.1 + Math.sin(progress.current * Math.PI * 2) * 0.15;
    }
  });

  return (
    <primitive
      object={new THREE.Line(geometry, new THREE.LineBasicMaterial({ color: "#22d3ee", transparent: true, opacity: 0.2 }))}
      ref={arcRef}
    />
  );
}

/* ── Floating 3D "phone" shape ── */
function FloatingDevice({ position, rotationOffset }: { position: [number, number, number]; rotationOffset: number }) {
  const group = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (group.current) {
      group.current.position.y = position[1] + Math.sin(state.clock.elapsedTime * 0.8 + rotationOffset) * 0.2;
      group.current.rotation.y = state.clock.elapsedTime * 0.3 + rotationOffset;
    }
  });

  return (
    <group ref={group} position={position}>
      {/* Phone body */}
      <mesh>
        <boxGeometry args={[0.28, 0.52, 0.05]} />
        <meshStandardMaterial color="#0a1628" metalness={0.9} roughness={0.1} />
      </mesh>
      {/* Screen */}
      <mesh position={[0, 0, 0.03]}>
        <boxGeometry args={[0.22, 0.44, 0.01]} />
        <meshStandardMaterial color="#0ea5e9" emissive="#0ea5e9" emissiveIntensity={0.6} roughness={0.1} />
      </mesh>
      {/* OTP digits on screen */}
      {[-0.07, -0.02, 0.03, 0.08].map((x, i) => (
        <mesh key={i} position={[x, 0.02, 0.04]}>
          <boxGeometry args={[0.032, 0.06, 0.005]} />
          <meshStandardMaterial color="#ffffff" emissive="#ffffff" emissiveIntensity={1} />
        </mesh>
      ))}
      {/* Notification dot */}
      <mesh position={[0.1, 0.22, 0.035]}>
        <sphereGeometry args={[0.025, 8, 8]} />
        <meshStandardMaterial color="#22c55e" emissive="#22c55e" emissiveIntensity={1} />
      </mesh>
    </group>
  );
}

/* ── Globe surface glow dots (US coverage points) ── */
function SurfaceDots() {
  const mesh = useRef<THREE.Points>(null);
  const count = 120;

  const positions = useMemo(() => {
    const pos = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      const theta = Math.random() * Math.PI * 2;
      const phi = Math.acos(2 * Math.random() - 1);
      const r = 2.05;
      pos[i * 3] = r * Math.sin(phi) * Math.cos(theta);
      pos[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
      pos[i * 3 + 2] = r * Math.cos(phi);
    }
    return pos;
  }, []);

  useFrame((state) => {
    if (mesh.current) {
      (mesh.current.material as THREE.PointsMaterial).opacity =
        0.5 + Math.sin(state.clock.elapsedTime * 1.5) * 0.3;
    }
  });

  return (
    <points ref={mesh}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" array={positions} count={count} itemSize={3} />
      </bufferGeometry>
      <pointsMaterial size={0.04} color="#22d3ee" transparent opacity={0.7} sizeAttenuation />
    </points>
  );
}

/* ── Orbiting ring ── */
function OrbitRing() {
  const mesh = useRef<THREE.Mesh>(null);
  useFrame((state) => {
    if (mesh.current) {
      mesh.current.rotation.z = state.clock.elapsedTime * 0.15;
      mesh.current.rotation.x = 0.5 + Math.sin(state.clock.elapsedTime * 0.1) * 0.1;
    }
  });
  return (
    <mesh ref={mesh} rotation={[Math.PI / 2.5, 0, 0]}>
      <torusGeometry args={[3.1, 0.008, 4, 120]} />
      <meshBasicMaterial color="#0ea5e9" transparent opacity={0.25} />
    </mesh>
  );
}

/* ── Background particles ── */
function ParticleDust() {
  const mesh = useRef<THREE.Points>(null);
  const count = 1200;
  const positions = useMemo(() => {
    const pos = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      const r = 4 + Math.random() * 6;
      const theta = Math.random() * Math.PI * 2;
      const phi = Math.acos(2 * Math.random() - 1);
      pos[i * 3] = r * Math.sin(phi) * Math.cos(theta);
      pos[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
      pos[i * 3 + 2] = r * Math.cos(phi);
    }
    return pos;
  }, []);

  useFrame((state) => {
    if (mesh.current) mesh.current.rotation.y = state.clock.elapsedTime * 0.02;
  });

  return (
    <points ref={mesh}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" array={positions} count={count} itemSize={3} />
      </bufferGeometry>
      <pointsMaterial size={0.018} color="#64748b" transparent opacity={0.6} sizeAttenuation />
    </points>
  );
}

function CameraRig() {
  useFrame((state) => {
    state.camera.position.x += (state.mouse.x * 0.6 - state.camera.position.x) * 0.025;
    state.camera.position.y += (state.mouse.y * 0.4 - state.camera.position.y) * 0.025;
    state.camera.lookAt(0, 0, 0);
  });
  return null;
}

function Scene() {
  return (
    <>
      <color attach="background" args={["#060d1a"]} />
      <ambientLight intensity={0.2} />
      <pointLight position={[5, 5, 5]} intensity={3} color="#22d3ee" />
      <pointLight position={[-5, 3, -3]} intensity={2} color="#818cf8" />
      <pointLight position={[0, -5, 2]} intensity={1} color="#0ea5e9" />
      <pointLight position={[0, 0, 4]} intensity={1.5} color="#0ea5e9" />

      <Stars radius={50} depth={30} count={1500} factor={2.5} fade speed={0.4} />

      <OTPGlobe />
      <SurfaceDots />
      <OrbitRing />
      <ParticleDust />

      {/* OTP packets orbiting the globe */}
      <OTPPacket startAngle={0} speed={1.2} color="#22d3ee" />
      <OTPPacket startAngle={2.1} speed={0.9} color="#818cf8" />
      <OTPPacket startAngle={4.2} speed={1.5} color="#34d399" />
      <OTPPacket startAngle={1.0} speed={0.7} color="#f472b6" />
      <OTPPacket startAngle={3.5} speed={1.1} color="#fb923c" />

      {/* Floating phone devices */}
      <FloatingDevice position={[3.5, 0.5, 0]} rotationOffset={0} />
      <FloatingDevice position={[-3.3, 0.8, 0.5]} rotationOffset={2.1} />
      <FloatingDevice position={[1.5, -2.5, 1]} rotationOffset={4.2} />

      {/* Connection arcs */}
      <ConnectionArc from={[1.8, 0.8, 0.7]} to={[3.3, 0.4, 0]} />
      <ConnectionArc from={[-1.8, 0.6, 0.9]} to={[-3.1, 0.7, 0.4]} />

      <CameraRig />
    </>
  );
}

export function HeroScene() {
  return (
    <div className="absolute inset-0 w-full h-full">
      <SceneErrorBoundary fallback={<div className="absolute inset-0" />}>
        <Canvas
          camera={{ position: [0, 0, 8], fov: 52 }}
          gl={{ antialias: true, alpha: false, powerPreference: "high-performance" }}
          dpr={[1, 1.5]}
        >
          <Suspense fallback={null}>
            <Scene />
          </Suspense>
        </Canvas>
      </SceneErrorBoundary>
    </div>
  );
}
